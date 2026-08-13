<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\WhatsAppControlCenterController;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Log as ActivityLog;
use App\Models\WhatsAppBatch;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppBatchService;
use App\Services\WhatsAppService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WhatsAppBatchControlTest extends TestCase
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
        $this->createTables();
    }

    public function test_legacy_batch_provenance_resolves_a_durable_batch_without_changing_content(): void
    {
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'sent_by' => 'admin:manual|batch:11111111-1111-4111-8111-111111111111',
            'message' => 'original receipt content',
        ]));

        $batch = app(WhatsAppBatchService::class)->resolveForMessage($message);

        $this->assertSame('11111111-1111-4111-8111-111111111111', $batch->uuid);
        $this->assertSame($batch->id, $message->fresh()->batch_id);
        $this->assertSame('original receipt content', $message->fresh()->message);
        $this->assertSame(1, WhatsAppBatch::query()->count());
    }

    public function test_cancellation_is_idempotent_and_changes_only_pending_messages(): void
    {
        $batch = WhatsAppBatch::query()->create([
            'uuid' => '22222222-2222-4222-8222-222222222222',
            'source' => 'manual',
            'title' => 'Manual batch',
            'status' => 'running',
        ]);
        foreach (['pending', 'pending', 'sending', 'sent', 'failed'] as $status) {
            WhatsAppMessageLog::query()->create($this->messageAttributes([
                'batch_id' => $batch->id,
                'status' => $status,
            ]));
        }

        $preview = app(WhatsAppBatchService::class)->cancelPreview($batch);
        $result = app(WhatsAppBatchService::class)->cancel($batch, 7, 'operator request');
        $again = app(WhatsAppBatchService::class)->cancel($batch, 7, 'operator request');

        $this->assertSame(2, $preview['pending']);
        $this->assertSame(2, $result['cancelled']);
        $this->assertSame(2, $again['cancelled']);
        $this->assertSame(2, $batch->messages()->where('status', 'cancelled')->count());
        $this->assertSame(1, $batch->messages()->where('status', 'sending')->count());
        $this->assertSame(1, $batch->messages()->where('status', 'sent')->count());
        $this->assertSame(1, $batch->messages()->where('status', 'failed')->count());
        $this->assertSame('cancelled', $batch->fresh()->status);
    }

    public function test_worker_does_not_call_provider_for_cancelled_message_or_batch(): void
    {
        $batch = WhatsAppBatch::query()->create([
            'uuid' => '33333333-3333-4333-8333-333333333333',
            'source' => 'manual',
            'title' => 'Cancelled batch',
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
            'status' => 'pending',
        ]));
        $service = $this->trackingService();

        (new SendWhatsAppMessage($message->id))->handle($service);

        $this->assertSame(0, $service->statusCalls);
        $this->assertSame(0, $service->sendCalls);
        $this->assertSame('pending', $message->fresh()->status);
    }

    public function test_worker_atomically_rechecks_cancellation_after_provider_status_before_send(): void
    {
        $batch = WhatsAppBatch::query()->create([
            'uuid' => 'race-cancellation-batch',
            'source' => 'manual',
            'title' => 'Race cancellation batch',
            'status' => 'queued',
        ]);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
        ]));
        $service = new class($batch) extends WhatsAppService
        {
            public int $sendCalls = 0;

            public function __construct(private WhatsAppBatch $batch) {}

            public function status(): array
            {
                app(WhatsAppBatchService::class)->cancel($this->batch, 7, 'cancelled during status');

                return ['connected' => true];
            }

            public function send($phone, $message, array $options = []): array
            {
                $this->sendCalls++;

                return ['success' => true];
            }
        };

        (new SendWhatsAppMessage($message->id))->handle($service);

        $this->assertSame(0, $service->sendCalls);
        $this->assertSame('cancelled', $message->fresh()->status);
        $this->assertSame('cancelled', $batch->fresh()->status);
    }

    public function test_cancellation_after_claim_reports_sending_and_allows_provider_completion(): void
    {
        $batch = $this->batch(['uuid' => 'cancel-after-claim']);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id]));
        $service = new class($batch) extends WhatsAppService
        {
            public array $cancelCounts = [];

            public function __construct(private WhatsAppBatch $batch) {}

            public function status(): array
            {
                return ['connected' => true];
            }

            public function send($phone, $message, array $options = []): array
            {
                $this->cancelCounts = app(WhatsAppBatchService::class)
                    ->cancel($this->batch, 7, 'cancelled after claim');

                return ['success' => true];
            }
        };

        (new SendWhatsAppMessage($message->id))->handle($service);

        $this->assertSame(0, $service->cancelCounts['cancelled']);
        $this->assertSame(1, $service->cancelCounts['sending']);
        $this->assertSame('sent', $message->fresh()->status);
        $this->assertSame('cancelled', $batch->fresh()->status);

        $preview = app(WhatsAppControlCenterController::class)
            ->cancelBatchPreview($batch, app(WhatsAppBatchService::class))
            ->getData(true);
        $this->assertSame(
            'سيتم إلغاء الرسائل المنتظرة فقط. الرسائل الجاري إرسالها ستبقى بحالة «جارٍ الإرسال» وقد يكتمل إرسالها، والرسائل المرسلة لا يمكن استرجاعها.',
            $preview['warning']
        );
    }

    public function test_dedicated_pause_preserves_pending_message_without_provider_call(): void
    {
        DB::table('app_config')->insert([
            'key' => 'whatsapp_queue_paused',
            'value' => '1',
        ]);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes());
        $service = $this->trackingService();

        (new SendWhatsAppMessage($message->id))->handle($service);

        $this->assertSame(0, $service->statusCalls);
        $this->assertSame(0, $service->sendCalls);
        $this->assertSame('pending', $message->fresh()->status);
    }

    public function test_failed_callback_while_paused_preserves_pending_and_nonterminal_lifecycle(): void
    {
        DB::table('app_config')->insert(['key' => 'whatsapp_queue_paused', 'value' => '1']);
        $batch = $this->batch(['uuid' => 'paused-deadline', 'status' => 'running']);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id]));

        (new SendWhatsAppMessage($message->id))->failed(new \RuntimeException('deadline'));

        $this->assertSame('pending', $message->fresh()->status);
        $this->assertSame('running', $batch->fresh()->status);
    }

    public function test_failed_callback_for_never_claimed_payload_leaves_pending_for_recovery(): void
    {
        $batch = $this->batch(['uuid' => 'normal-deadline', 'status' => 'running']);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id]));

        (new SendWhatsAppMessage($message->id))->failed(new \RuntimeException('deadline'));

        $this->assertSame('pending', $message->fresh()->status);
        $this->assertSame('running', $batch->fresh()->status);
    }

    public function test_batch_control_routes_require_specific_permission(): void
    {
        foreach ([
            'admin.whatsapp.queue.pause',
            'admin.whatsapp.queue.batches.cancel_preview',
            'admin.whatsapp.queue.batches.cancel',
            'admin.whatsapp.queue.batches.retry',
            'admin.whatsapp.queue.batches.archive',
            'admin.whatsapp.log.resend',
        ] as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertContains('can:control_whatsapp_queue', $route->gatherMiddleware(), $name);
        }
    }

    public function test_legacy_resend_rejects_cancelled_batch_and_ambiguous_without_acknowledgement(): void
    {
        $dispatcher = app(\App\Services\WhatsApp\WhatsAppMessageDispatcher::class);
        $cancelled = $this->batch(['uuid' => 'legacy-cancelled', 'status' => 'cancelled', 'cancelled_at' => now()]);
        $cancelledMessage = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $cancelled->id,
            'status' => 'failed',
        ]));
        $ambiguousBatch = $this->batch(['uuid' => 'legacy-ambiguous', 'status' => 'completed_with_errors']);
        $ambiguous = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $ambiguousBatch->id,
            'status' => 'failed',
            'error' => 'Ambiguous delivery; automatic resend suppressed',
        ]));

        $cancelledResponse = app(WhatsAppControlCenterController::class)
            ->resendMessage(request(), $cancelledMessage->id, $dispatcher, app(WhatsAppBatchService::class));
        $ambiguousResponse = app(WhatsAppControlCenterController::class)
            ->resendMessage(request(), $ambiguous->id, $dispatcher, app(WhatsAppBatchService::class));

        $this->assertSame(409, $cancelledResponse->status());
        $this->assertSame('batch_not_retryable', $cancelledResponse->getData(true)['error']);
        $this->assertSame(409, $ambiguousResponse->status());
        $this->assertSame('ambiguous_acknowledgement_required', $ambiguousResponse->getData(true)['error']);
        $this->assertSame('failed', $cancelledMessage->fresh()->status);
        $this->assertSame('failed', $ambiguous->fresh()->status);
    }

    public function test_legacy_resend_requires_failed_status_and_acknowledgement_retries_one_message(): void
    {
        $dispatcher = app(\App\Services\WhatsApp\WhatsAppMessageDispatcher::class);
        $batch = $this->batch(['uuid' => 'legacy-one', 'status' => 'completed_with_errors']);
        $ambiguous = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
            'status' => 'failed',
            'error' => 'Ambiguous delivery; automatic resend suppressed',
        ]));
        $other = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
            'status' => 'failed',
            'error' => 'invalid phone',
        ]));
        $sent = WhatsAppMessageLog::query()->create($this->messageAttributes(['status' => 'sent']));

        $notFailed = app(WhatsAppControlCenterController::class)
            ->resendMessage(request(), $sent->id, $dispatcher, app(WhatsAppBatchService::class));
        $request = request()->duplicate(['acknowledge_ambiguous' => true]);
        $retried = app(WhatsAppControlCenterController::class)
            ->resendMessage($request, $ambiguous->id, $dispatcher, app(WhatsAppBatchService::class));

        $this->assertSame(409, $notFailed->status());
        $this->assertSame('message_not_retryable', $notFailed->getData(true)['error']);
        $this->assertTrue($retried->getData(true)['success']);
        $this->assertSame('pending', $ambiguous->fresh()->status);
        $this->assertSame('failed', $other->fresh()->status);
    }

    public function test_retry_refuses_cancelled_cancelling_and_archived_batches(): void
    {
        foreach (['cancelled', 'cancelling'] as $status) {
            $batch = $this->batch(['uuid' => 'retry-'.$status, 'status' => $status]);
            $message = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id, 'status' => 'failed']));
            try {
                app(WhatsAppBatchService::class)->retryFailed($batch);
                $this->fail('Inactive batch retry should conflict.');
            } catch (\DomainException $exception) {
                $this->assertSame('batch_not_retryable', $exception->getMessage());
            }
            $this->assertSame('failed', $message->fresh()->status);
        }

        $batch = $this->batch(['uuid' => 'retry-archived', 'status' => 'completed_with_errors', 'archived_at' => now()]);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id, 'status' => 'failed']));
        try {
            app(WhatsAppBatchService::class)->retryFailed($batch);
            $this->fail('Archived batch retry should conflict.');
        } catch (\DomainException $exception) {
            $this->assertSame('batch_not_retryable', $exception->getMessage());
        }
        $this->assertSame('failed', $message->fresh()->status);
    }

    public function test_retry_route_returns_stable_zero_contract_for_cancelled_batch(): void
    {
        $batch = $this->batch(['uuid' => 'retry-route-cancelled', 'status' => 'cancelled', 'cancelled_at' => now()]);
        WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id, 'status' => 'failed']));
        $response = app(WhatsAppControlCenterController::class)->retryBatch(
            request(),
            $batch,
            app(WhatsAppBatchService::class),
            app(\App\Services\WhatsApp\WhatsAppMessageDispatcher::class)
        );
        $this->assertSame(409, $response->status());
        $this->assertSame(['success' => false, 'queued' => 0, 'error' => 'batch_not_retryable'], $response->getData(true));
    }

    public function test_lifecycle_runs_on_claim_and_completes_after_success_or_failure(): void
    {
        foreach ([
            ['uuid' => 'life-success', 'result' => ['success' => true], 'expected' => 'completed'],
            ['uuid' => 'life-failure', 'result' => ['success' => false, 'status_code' => 400, 'error' => 'bad'], 'expected' => 'completed_with_errors'],
            ['uuid' => 'life-ambiguous', 'result' => ['success' => false, 'ambiguous_delivery' => true, 'error' => 'unknown'], 'expected' => 'completed_with_errors'],
        ] as $case) {
            $batch = $this->batch(['uuid' => $case['uuid']]);
            $message = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id]));
            $service = $this->resultService($case['result']);
            (new SendWhatsAppMessage($message->id))->handle($service);
            $this->assertSame($case['expected'], $batch->fresh()->status);
        }
    }


    public function test_failed_callback_preserves_cancelled_message_and_batch(): void
    {
        $batch = $this->batch(['uuid' => 'life-cancelled-callback', 'status' => 'cancelled', 'cancelled_at' => now()]);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id, 'status' => 'cancelled']));
        (new SendWhatsAppMessage($message->id))->failed(new \RuntimeException('late callback'));
        $this->assertSame('cancelled', $message->fresh()->status);
        $this->assertSame('cancelled', $batch->fresh()->status);
    }

    public function test_cancel_rejects_completed_batch_but_is_idempotent_when_cancelled(): void
    {
        $completed = $this->batch(['uuid' => 'cancel-completed', 'status' => 'completed']);
        try {
            app(WhatsAppBatchService::class)->cancel($completed, 7);
            $this->fail('Completed cancellation should conflict');
        } catch (\DomainException $exception) {
            $this->assertSame('batch_not_cancellable', $exception->getMessage());
        }
        $this->assertSame('completed', $completed->fresh()->status);

        $cancelled = $this->batch(['uuid' => 'cancel-idempotent', 'status' => 'cancelled', 'cancelled_at' => now()]);
        $this->assertSame(0, app(WhatsAppBatchService::class)->cancel($cancelled, 7)['total']);
    }

    public function test_cancel_route_returns_conflict_for_completed_batch(): void
    {
        $batch = $this->batch(['uuid' => 'cancel-route-completed', 'status' => 'completed']);
        $response = app(WhatsAppControlCenterController::class)->cancelBatch(
            request(),
            $batch,
            app(WhatsAppBatchService::class)
        );
        $this->assertSame(409, $response->status());
        $this->assertSame(['success' => false, 'error' => 'batch_not_cancellable'], $response->getData(true));
    }

    public function test_legacy_non_uuid_long_keys_are_deterministically_unique(): void
    {
        $keyA = str_repeat('legacy/non-rfc-key-', 20).'A';
        $keyB = str_repeat('legacy/non-rfc-key-', 20).'B';
        $a1 = WhatsAppMessageLog::query()->create($this->messageAttributes(['sent_by' => 'admin:manual|batch:'.$keyA]));
        $a2 = WhatsAppMessageLog::query()->create($this->messageAttributes(['sent_by' => 'admin:manual|batch:'.$keyA]));
        $b = WhatsAppMessageLog::query()->create($this->messageAttributes(['sent_by' => 'admin:manual|batch:'.$keyB]));
        $service = app(WhatsAppBatchService::class);
        $resolvedA = $service->resolveForMessage($a1);
        $this->assertSame($resolvedA->id, $service->resolveForMessage($a2)->id);
        $this->assertNotSame($resolvedA->id, $service->resolveForMessage($b)->id);
        $this->assertSame(36, strlen($resolvedA->uuid));
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $resolvedA->uuid);
        $this->assertSame(2, WhatsAppBatch::query()->count());
    }

    public function test_activity_log_casts_json_audit_data_as_array(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->text('description');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        $log = ActivityLog::query()->create([
            'action' => 'whatsapp_batch_retried',
            'description' => 'audit',
            'new_data' => json_encode(['queued' => 0], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
        $this->assertSame(['queued' => 0], json_decode($log->fresh()->new_data, true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_multi_message_lifecycle_does_not_terminalize_while_retry_reopens_work(): void
    {
        $batch = $this->batch(['uuid' => 'interleaved-lifecycle', 'status' => 'running']);
        $failed = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
            'status' => 'failed',
        ]));
        $pending = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
            'status' => 'pending',
        ]));

        app(WhatsAppBatchService::class)->updateLifecycleForMessage($failed->id);
        $this->assertSame('running', $batch->fresh()->status);

        $retried = app(WhatsAppBatchService::class)->retryFailed($batch);
        app(WhatsAppBatchService::class)->updateLifecycleForMessage($pending->id);

        $this->assertCount(1, $retried);
        $this->assertSame('pending', $failed->fresh()->status);
        $this->assertSame('queued', $batch->fresh()->status);
    }

    public function test_audit_failure_rolls_back_pause_and_batch_mutations(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->text('description');
            $table->json('new_data')->nullable();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        DB::statement("CREATE TRIGGER reject_whatsapp_audit BEFORE INSERT ON logs BEGIN SELECT RAISE(FAIL, 'audit rejected'); END");
        $controller = app(WhatsAppControlCenterController::class);

        try {
            $controller->toggleQueuePause(app(\App\Services\WhatsApp\WhatsAppQueueState::class));
            $this->fail('Pause audit should fail');
        } catch (\Throwable $exception) {
            $this->assertStringContainsString('audit rejected', $exception->getMessage());
        }
        $this->assertFalse(app(\App\Services\WhatsApp\WhatsAppQueueState::class)->paused());

        $cancel = $this->batch(['uuid' => 'audit-cancel']);
        $cancelMessage = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $cancel->id]));
        try {
            $controller->cancelBatch(request(), $cancel, app(WhatsAppBatchService::class));
            $this->fail('Cancel audit should fail');
        } catch (\Throwable $exception) {
            $this->assertStringContainsString('audit rejected', $exception->getMessage());
        }
        $this->assertSame('queued', $cancel->fresh()->status);
        $this->assertSame('pending', $cancelMessage->fresh()->status);

        $archive = $this->batch(['uuid' => 'audit-archive', 'status' => 'completed']);
        try {
            $controller->archiveBatch($archive, app(WhatsAppBatchService::class));
            $this->fail('Archive audit should fail');
        } catch (\Throwable $exception) {
            $this->assertStringContainsString('audit rejected', $exception->getMessage());
        }
        $this->assertNull($archive->fresh()->archived_at);
    }

    public function test_archive_service_rejects_non_terminal_batch(): void
    {
        $batch = $this->batch(['uuid' => 'archive-running', 'status' => 'running']);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('batch_not_archivable');
        app(WhatsAppBatchService::class)->archive($batch, 7);
    }

    public function test_archive_rechecks_terminal_state_and_messages_at_mutation_time(): void
    {
        $batch = $this->batch(['uuid' => 'archive-stale-copy', 'status' => 'completed_with_errors']);
        $staleControllerCopy = $batch->fresh();
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
            'status' => 'failed',
        ]));

        $message->update(['status' => 'pending']);
        $batch->update(['status' => 'queued']);

        try {
            app(WhatsAppBatchService::class)->archive($staleControllerCopy, 7);
            $this->fail('Archive must reject work reopened after a stale controller read.');
        } catch (\DomainException $exception) {
            $this->assertSame('batch_not_archivable', $exception->getMessage());
        }

        $this->assertNull($batch->fresh()->archived_at);
        $this->assertSame('pending', $message->fresh()->status);
    }

    public function test_retry_after_archive_is_rejected_by_the_shared_batch_lock_contract(): void
    {
        $batch = $this->batch(['uuid' => 'archive-before-retry', 'status' => 'completed_with_errors']);
        WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
            'status' => 'failed',
        ]));

        app(WhatsAppBatchService::class)->archive($batch, 7);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('batch_not_retryable');
        app(WhatsAppBatchService::class)->retryFailed($batch);
    }

    public function test_rehydrated_original_payload_cannot_overwrite_operator_retry(): void
    {
        $batch = $this->batch(['uuid' => 'stale-failed-after-retry', 'status' => 'running']);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
        ]));
        $originalPayload = serialize(new SendWhatsAppMessage($message->id));
        $runningCopy = unserialize($originalPayload);

        $claimed = app(WhatsAppBatchService::class)->claimForDelivery(
            $message->id,
            $runningCopy->attemptToken
        );
        $this->assertNotNull($claimed);
        app(WhatsAppBatchService::class)->transitionClaimedMessage(
            $message->id,
            $runningCopy->attemptToken,
            'failed',
            'operator can retry'
        );

        app(WhatsAppBatchService::class)->retryMessage($message->fresh());
        $this->assertNull($message->fresh()->delivery_token);

        $callbackCopy = unserialize($originalPayload);
        $callbackCopy->failed(new \RuntimeException('late stale callback'));

        $this->assertSame('pending', $message->fresh()->status);
        $this->assertSame('queued', $batch->fresh()->status);
    }

    public function test_old_rehydrated_callback_cannot_overwrite_a_newer_claim(): void
    {
        $batch = $this->batch(['uuid' => 'old-callback-new-claim', 'status' => 'running']);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id]));
        $oldPayload = serialize(new SendWhatsAppMessage($message->id));
        $oldRunningCopy = unserialize($oldPayload);
        $service = app(WhatsAppBatchService::class);

        $service->claimForDelivery($message->id, $oldRunningCopy->attemptToken);
        $service->transitionClaimedMessage($message->id, $oldRunningCopy->attemptToken, 'failed', 'retryable');
        $service->retryMessage($message->fresh());

        $newPayload = serialize(new SendWhatsAppMessage($message->id));
        $newRunningCopy = unserialize($newPayload);
        $service->claimForDelivery($message->id, $newRunningCopy->attemptToken);
        unserialize($oldPayload)->failed(new \RuntimeException('old callback'));

        $fresh = $message->fresh();
        $this->assertSame('sending', $fresh->status);
        $this->assertSame($newRunningCopy->attemptToken, $fresh->delivery_token);
        $this->assertNotSame($oldRunningCopy->attemptToken, $fresh->delivery_token);
    }

    public function test_cancellation_after_claim_does_not_reopen_rate_limited_message(): void
    {
        $this->assertCancellationAfterClaimPreservesSending([
            'success' => false,
            'rate_limited' => true,
            'error' => 'rate limited after claim',
            'retry_after_seconds' => 60,
        ]);
    }

    public function test_cancellation_after_claim_does_not_reopen_transient_failure(): void
    {
        $this->assertCancellationAfterClaimPreservesSending([
            'success' => false,
            'error' => 'transient after claim',
        ]);
    }

    public function test_archive_is_idempotent_and_retry_is_scoped_and_conservative(): void
    {
        $batch = WhatsAppBatch::query()->create([
            'uuid' => '44444444-4444-4444-8444-444444444444',
            'source' => 'manual',
            'title' => 'Completed batch',
            'status' => 'completed_with_errors',
        ]);
        WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
            'status' => 'failed',
            'error' => 'Invalid phone',
        ]));
        WhatsAppMessageLog::query()->create($this->messageAttributes([
            'batch_id' => $batch->id,
            'status' => 'failed',
            'error' => 'Ambiguous delivery; automatic resend suppressed',
        ]));
        $other = WhatsAppMessageLog::query()->create($this->messageAttributes(['status' => 'failed']));

        $archiveBatch = WhatsAppBatch::query()->create([
            'uuid' => '44444444-4444-4444-8444-444444444445',
            'source' => 'manual',
            'title' => 'Completed batch archive copy',
            'status' => 'completed',
        ]);
        $retried = app(WhatsAppBatchService::class)->retryFailed($batch, false);
        $firstArchive = app(WhatsAppBatchService::class)->archive($archiveBatch, 7);
        $secondArchive = app(WhatsAppBatchService::class)->archive($archiveBatch, 7);

        $this->assertSame(1, $retried->count());
        $this->assertSame('pending', $retried->first()->status);
        $this->assertSame('failed', $batch->messages()->where('error', 'like', 'Ambiguous%')->first()->status);
        $this->assertSame('failed', $other->fresh()->status);
        $this->assertNotNull($firstArchive->archived_at);
        $this->assertSame($firstArchive->archived_at->toDateTimeString(), $secondArchive->archived_at->toDateTimeString());
    }

    private function assertCancellationAfterClaimPreservesSending(array $result): void
    {
        $batch = $this->batch(['uuid' => 'cancel-claimed-'.md5(json_encode($result))]);
        $message = WhatsAppMessageLog::query()->create($this->messageAttributes(['batch_id' => $batch->id]));
        $service = new class($batch, $result) extends WhatsAppService
        {
            public function __construct(private WhatsAppBatch $batch, private array $result) {}
            public function status(): array { return ['connected' => true]; }
            public function send($phone, $message, array $options = []): array
            {
                app(WhatsAppBatchService::class)->cancel($this->batch, 7, 'cancelled during provider call');

                return $this->result;
            }
        };

        try {
            (new SendWhatsAppMessage($message->id))->handle($service);
        } catch (\RuntimeException $exception) {
            $this->assertSame($result['error'], $exception->getMessage());
        }

        $this->assertSame('sending', $message->fresh()->status);
        $this->assertSame('cancelled', $batch->fresh()->status);
    }

    private function batch(array $overrides = []): WhatsAppBatch
    {
        return WhatsAppBatch::query()->create(array_merge([
            'uuid' => 'batch-default',
            'source' => 'manual',
            'title' => 'Batch',
            'status' => 'queued',
        ], $overrides));
    }

    private function resultService(array $result): WhatsAppService
    {
        return new class($result) extends WhatsAppService
        {
            public function __construct(private array $result) {}
            public function status(): array { return ['connected' => true]; }
            public function send($phone, $message, array $options = []): array { return $this->result; }
        };
    }

    private function trackingService(): WhatsAppService
    {
        return new class extends WhatsAppService
        {
            public int $statusCalls = 0;
            public int $sendCalls = 0;

            public function status(): array
            {
                $this->statusCalls++;

                return ['connected' => true];
            }

            public function send($phone, $message, array $options = []): array
            {
                $this->sendCalls++;

                return ['success' => true];
            }
        };
    }

    private function messageAttributes(array $overrides = []): array
    {
        return array_merge([
            'client_id' => 1,
            'client_name' => 'Customer',
            'phone' => '96170000000',
            'message' => 'Message',
            'template_type' => 'reminder',
            'status' => 'pending',
            'sent_by' => 'admin:manual|batch:test-batch',
        ], $overrides);
    }

    private function createTables(): void
    {
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
        Schema::create('whatsapp_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('source');
            $table->string('title');
            $table->string('template_type')->nullable();
            $table->string('status')->default('queued');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
        Schema::create('whatsapp_message_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_name')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->text('invoice_ids')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('phone');
            $table->text('message');
            $table->string('template_type')->nullable();
            $table->string('sent_by')->nullable();
            $table->string('status')->default('pending');
            $table->uuid('delivery_token')->nullable()->index();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }
}
