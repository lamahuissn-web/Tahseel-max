<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\WhatsAppControlCenterController;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Admin\Invoice;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\PaymentReceiptNotifier;
use App\Services\WhatsApp\WhatsAppMessageDispatcher;
use App\Services\WhatsApp\WhatsAppRateLimiter;
use App\Services\WhatsApp\WhatsAppSendLock;
use App\Services\WhatsAppService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WhatsAppStabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
        ]);
        DB::purge();
        Cache::flush();
        $this->createTables();
    }

    public function test_receipt_uses_latest_revenue_and_dispatches_a_durable_job(): void
    {
        Queue::fake();
        $invoice = $this->createPaidInvoiceWithTwoRevenues();

        app(PaymentReceiptNotifier::class)->notify($invoice);

        $log = WhatsAppMessageLog::query()->sole();
        $this->assertSame('pending', $log->status);
        $this->assertStringContainsString('$15.00', $log->message);
        $this->assertStringContainsString('Latest Collector', $log->message);
        $this->assertStringNotContainsString('Old Collector', $log->message);
        Queue::assertPushed(SendWhatsAppMessage::class, fn ($job) => $job->messageLogId === $log->id);
    }

    public function test_successful_receipt_delivery_does_not_mutate_invoice_notification_metadata(): void
    {
        Queue::fake();
        $invoice = $this->createPaidInvoiceWithTwoRevenues();
        $invoice->update(['last_notified_at' => '2026-01-01 12:00:00']);
        app(PaymentReceiptNotifier::class)->notify($invoice);
        $log = WhatsAppMessageLog::query()->sole();

        (new SendWhatsAppMessage($log->id))->handle(
            $this->fakeWhatsAppService(['connected' => true], ['success' => true])
        );

        $this->assertSame(
            '2026-01-01 12:00:00',
            DB::table('tbl_invoices')->where('id', $invoice->id)->value('last_notified_at')
        );
    }

    public function test_resend_adds_recoverable_batch_provenance_before_dispatch(): void
    {
        Queue::fake();
        $log = WhatsAppMessageLog::query()->create(array_merge($this->messageAttributes(), [
            'status' => 'failed',
            'sent_by' => 'admin:1',
        ]));

        $response = app(WhatsAppControlCenterController::class)->resendMessage(
            $log->id,
            app(WhatsAppMessageDispatcher::class)
        );

        $this->assertTrue($response->getData(true)['success']);
        $this->assertSame('pending', $log->fresh()->status);
        $this->assertStringContainsString('|batch:', $log->fresh()->sent_by);
        Queue::assertPushed(SendWhatsAppMessage::class, 1);
    }

    public function test_dispatcher_uses_the_dedicated_database_queue_after_commit(): void
    {
        Queue::fake();
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());

        app(WhatsAppMessageDispatcher::class)->dispatch($log);

        Queue::assertPushedOn('whatsapp', SendWhatsAppMessage::class);
        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) use ($log) {
            return $job->connection === 'whatsapp_database'
                && $job->messageLogId === $log->id;
        });
    }

    public function test_duplicate_dispatch_creates_one_durable_database_job(): void
    {
        Cache::flush();
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());
        $dispatcher = app(WhatsAppMessageDispatcher::class);

        $dispatcher->dispatch($log);
        $dispatcher->dispatch($log);

        $this->assertSame(1, DB::table('jobs')->count());
        $this->assertSame('whatsapp', DB::table('jobs')->value('queue'));
        $payload = json_decode((string) DB::table('jobs')->value('payload'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(0, $payload['maxTries']);
        $this->assertGreaterThan(time(), $payload['retryUntil']);
    }

    public function test_failed_queue_insertion_releases_unique_lock_for_recovery(): void
    {
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());
        Schema::rename('jobs', 'jobs_temporarily_unavailable');

        app(WhatsAppMessageDispatcher::class)->dispatch($log);

        Schema::rename('jobs_temporarily_unavailable', 'jobs');
        $this->assertSame(0, Artisan::call('whatsapp:recover-pending'));
        $this->assertSame(1, DB::table('jobs')->count());
    }

    public function test_dispatch_is_cancelled_when_the_owning_transaction_rolls_back(): void
    {
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());

        DB::beginTransaction();
        app(WhatsAppMessageDispatcher::class)->dispatch($log);
        $this->assertSame(0, DB::table('jobs')->count());
        DB::rollBack();

        $this->assertSame(0, DB::table('jobs')->count());
    }

    public function test_real_database_worker_releases_disconnected_job_for_later_retry(): void
    {
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());
        app(WhatsAppMessageDispatcher::class)->dispatch($log);
        app()->instance(
            WhatsAppService::class,
            $this->fakeWhatsAppService(['connected' => false], ['success' => true])
        );

        $worker = new Worker(
            app('queue'),
            app('events'),
            app(\Illuminate\Contracts\Debug\ExceptionHandler::class),
            fn () => false
        );
        $worker->runNextJob(
            'whatsapp_database',
            'whatsapp',
            new WorkerOptions('whatsapp-test', 0, 128, 650, 0, 0, true)
        );

        $job = DB::table('jobs')->sole();
        $this->assertSame(1, (int) $job->attempts);
        $this->assertGreaterThanOrEqual(time() + 295, (int) $job->available_at);
        $this->assertSame('pending', $log->fresh()->status);
    }

    public function test_send_job_marks_a_successful_message_as_sent(): void
    {
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());
        $service = $this->fakeWhatsAppService(
            ['connected' => true],
            ['success' => true, 'message_id' => 'wamid-1']
        );

        (new SendWhatsAppMessage($log->id))->handle($service);

        $this->assertSame('sent', $log->fresh()->status);
        $this->assertNull($log->fresh()->error);
    }

    public function test_send_job_keeps_rate_limited_message_pending(): void
    {
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());
        $service = $this->fakeWhatsAppService(
            ['connected' => true],
            [
                'success' => false,
                'rate_limited' => true,
                'error' => 'Hourly cap reached',
                'retry_after_seconds' => 120,
            ]
        );

        (new SendWhatsAppMessage($log->id))->handle($service);

        $this->assertSame('pending', $log->fresh()->status);
        $this->assertSame('Hourly cap reached', $log->fresh()->error);
    }

    public function test_disconnected_openwa_keeps_receipt_pending_for_retry(): void
    {
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());
        $service = $this->fakeWhatsAppService(
            ['connected' => false],
            ['success' => true]
        );

        (new SendWhatsAppMessage($log->id))->handle($service);

        $this->assertSame('pending', $log->fresh()->status);
        $this->assertSame('WhatsApp is temporarily disconnected', $log->fresh()->error);
    }

    public function test_openwa_connection_exception_is_reported_as_ambiguous(): void
    {
        Http::fake(fn () => throw new ConnectionException('simulated transport timeout'));

        $result = app(WhatsAppService::class)->send('96170000000', 'test', [
            'skip_rate_limit' => true,
        ]);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['ambiguous_delivery']);
    }

    public function test_ambiguous_transport_result_suppresses_automatic_resend(): void
    {
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());
        $service = $this->fakeWhatsAppService(
            ['connected' => true],
            [
                'success' => false,
                'ambiguous_delivery' => true,
                'error' => 'simulated transport timeout',
            ]
        );

        (new SendWhatsAppMessage($log->id))->handle($service);

        $this->assertSame('failed', $log->fresh()->status);
        $this->assertStringContainsString('automatic resend suppressed', $log->fresh()->error);
    }

    public function test_transient_openwa_failure_is_retried_with_pending_status(): void
    {
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());
        $service = $this->fakeWhatsAppService(
            ['connected' => true],
            ['success' => false, 'error' => 'OpenWA timeout']
        );

        try {
            (new SendWhatsAppMessage($log->id))->handle($service);
            $this->fail('A transient OpenWA failure must be retried by the queue.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('OpenWA timeout', $exception->getMessage());
        }

        $this->assertSame('pending', $log->fresh()->status);
        $this->assertSame('OpenWA timeout', $log->fresh()->error);
    }

    public function test_permanent_openwa_client_error_fails_without_retry(): void
    {
        $log = WhatsAppMessageLog::query()->create($this->messageAttributes());
        $service = $this->fakeWhatsAppService(
            ['connected' => true],
            ['success' => false, 'status_code' => 422, 'error' => 'Invalid phone']
        );

        (new SendWhatsAppMessage($log->id))->handle($service);

        $this->assertSame('failed', $log->fresh()->status);
        $this->assertSame('Invalid phone', $log->fresh()->error);
    }

    public function test_stale_sending_receipt_is_failed_as_ambiguous_instead_of_duplicated(): void
    {
        $log = WhatsAppMessageLog::query()->create(array_merge($this->messageAttributes(), [
            'status' => 'sending',
            'updated_at' => now()->subMinutes(16),
        ]));
        $service = $this->fakeWhatsAppService(['connected' => true], ['success' => true]);

        (new SendWhatsAppMessage($log->id))->handle($service);

        $this->assertSame('failed', $log->fresh()->status);
        $this->assertStringContainsString('Ambiguous delivery', $log->fresh()->error);
    }

    public function test_batch_pause_returns_retry_time_without_sleeping_under_send_lock(): void
    {
        DB::table('app_config')->insert([
            ['key' => 'whatsapp_rate_batch_pause_every', 'value' => '1'],
            ['key' => 'whatsapp_rate_batch_pause_min_seconds', 'value' => '180'],
            ['key' => 'whatsapp_rate_batch_pause_max_seconds', 'value' => '180'],
        ]);
        WhatsAppMessageLog::query()->create(array_merge($this->messageAttributes(), [
            'status' => 'sent',
            'updated_at' => now(),
        ]));

        $startedAt = microtime(true);
        $result = app(WhatsAppRateLimiter::class)->waitBeforeSend();

        $this->assertFalse($result['allowed']);
        $this->assertTrue($result['rate_limited']);
        $this->assertSame(180, $result['retry_after_seconds']);
        $this->assertLessThan(1.0, microtime(true) - $startedAt);

        Cache::put('whatsapp_rate_limiter_batch_pause_until', time() - 1, 60);
        $sameCountResult = app(WhatsAppRateLimiter::class)->waitBeforeSend();
        $this->assertTrue($sameCountResult['allowed']);
    }

    public function test_legacy_batch_command_dispatches_durable_jobs_instead_of_sending_directly(): void
    {
        Queue::fake();
        WhatsAppMessageLog::query()->create(array_merge($this->messageAttributes(), [
            'sent_by' => 'admin:1|batch:legacy-batch',
        ]));

        $this->assertSame(0, Artisan::call('whatsapp:process-pending', ['batch' => 'legacy-batch']));

        Queue::assertPushed(SendWhatsAppMessage::class, 1);
    }

    public function test_global_send_lock_rejects_a_second_sender(): void
    {
        Cache::setDefaultDriver('array');
        $lock = new WhatsAppSendLock(0);
        $secondEntered = null;

        $firstEntered = $lock->run(function () use ($lock, &$secondEntered) {
            $secondEntered = $lock->run(fn () => 'second');

            return 'first';
        });

        $this->assertSame('first', $firstEntered);
        $this->assertNull($secondEntered);
    }

    public function test_whatsapp_service_does_not_call_openwa_when_send_lock_is_busy(): void
    {
        Http::fake();
        $lock = new WhatsAppSendLock(0);
        app()->instance(WhatsAppSendLock::class, $lock);

        $result = $lock->run(function () {
            return app(WhatsAppService::class)->send('96170000000', 'Receipt', [
                'skip_rate_limit' => true,
            ]);
        });

        $this->assertFalse($result['success']);
        $this->assertTrue($result['rate_limited']);
        Http::assertNothingSent();
    }

    public function test_recovery_command_redispatches_all_pending_batch_messages(): void
    {
        Queue::fake();
        WhatsAppMessageLog::query()->create($this->messageAttributes());
        WhatsAppMessageLog::query()->create(array_merge($this->messageAttributes(), [
            'template_type' => 'reminder',
            'sent_by' => 'system:reminder|batch:test',
        ]));

        $this->assertSame(0, Artisan::call('whatsapp:recover-pending'));

        Queue::assertPushed(SendWhatsAppMessage::class, 2);
    }

    public function test_recovery_cursor_rotates_past_the_first_limited_page(): void
    {
        Queue::fake();
        for ($index = 0; $index < 3; $index++) {
            WhatsAppMessageLog::query()->create($this->messageAttributes());
        }

        $this->assertSame(0, Artisan::call('whatsapp:recover-pending', ['--limit' => 2]));
        Queue::assertPushed(SendWhatsAppMessage::class, 2);

        $this->assertSame(0, Artisan::call('whatsapp:recover-pending', ['--limit' => 2]));
        Queue::assertPushed(SendWhatsAppMessage::class, 3);
    }

    private function createPaidInvoiceWithTwoRevenues(): Invoice
    {
        DB::table('tbl_clients')->insert([
            'id' => 1,
            'name' => 'Customer',
            'phone' => '96170000000',
        ]);
        DB::table('admins')->insert([
            ['id' => 1, 'name' => 'Old Collector'],
            ['id' => 2, 'name' => 'Latest Collector'],
        ]);
        DB::table('tbl_invoices')->insert([
            'id' => 1,
            'client_id' => 1,
            'amount' => 100,
            'paid_amount' => 25,
            'remaining_amount' => 75,
            'status' => 'partial',
            'due_date' => now()->subDay()->toDateString(),
            'paid_date' => now(),
        ]);
        DB::table('tbl_revenues')->insert([
            [
                'id' => 1,
                'invoice_id' => 1,
                'client_id' => 1,
                'amount' => 10,
                'collected_by' => 1,
                'received_at' => now()->subMinute(),
            ],
            [
                'id' => 2,
                'invoice_id' => 1,
                'client_id' => 1,
                'amount' => 15,
                'collected_by' => 2,
                'received_at' => now(),
            ],
        ]);

        return Invoice::query()->findOrFail(1);
    }

    private function fakeWhatsAppService(array $status, array $sendResult): WhatsAppService
    {
        return new class($status, $sendResult) extends WhatsAppService
        {
            public function __construct(
                private readonly array $fakeStatus,
                private readonly array $fakeSendResult
            ) {}

            public function status(): array
            {
                return $this->fakeStatus;
            }

            public function send($phone, $message, array $options = []): array
            {
                return $this->fakeSendResult;
            }
        };
    }

    private function messageAttributes(): array
    {
        return [
            'client_id' => 1,
            'client_name' => 'Customer',
            'phone' => '96170000000',
            'message' => 'Receipt',
            'template_type' => 'receipt',
            'status' => 'pending',
            'sent_by' => 'system:autoreceipt|batch:test',
        ];
    }

    private function createTables(): void
    {
        Schema::create('tbl_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('app_config', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::create('tbl_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->decimal('amount');
            $table->decimal('paid_amount')->default(0);
            $table->decimal('remaining_amount')->default(0);
            $table->string('status');
            $table->date('due_date')->nullable();
            $table->dateTime('paid_date')->nullable();
            $table->dateTime('last_notified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('tbl_revenues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('client_id');
            $table->decimal('amount');
            $table->unsignedBigInteger('collected_by');
            $table->dateTime('received_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
        Schema::create('whatsapp_message_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_name')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->text('invoice_ids')->nullable();
            $table->string('phone');
            $table->text('message');
            $table->string('template_type')->nullable();
            $table->string('sent_by')->nullable();
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }
}
