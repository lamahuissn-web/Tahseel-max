<?php

namespace Tests\Feature;

use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\MonthlyReminderNotifier;
use App\Services\WhatsApp\PaymentReceiptNotifier;
use App\Services\WhatsApp\WhatsAppMessageDispatcher;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\WrapsMysqlTransaction;

/**
 * Spec 019 — WhatsApp phone validation.
 *
 * Verifies that unsendable numbers (empty/garbage like '961000000') never
 * reach the provider:
 *  - Receipt: payment NOT blocked, but a FAILED log row is created with a
 *    visible reason and NO dispatch happens.
 *  - Monthly reminder: skipped like a missing phone, no row.
 *  - Delivery backstop: SendWhatsAppMessage::deliver() marks invalid rows
 *    failed without calling the provider.
 *
 * SAFETY: dispatcher mocked as no-op + global queue fake. Nothing can send.
 */
class WhatsAppPhoneValidationTest extends TestCase
{
    use WrapsMysqlTransaction;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=tahseel_new');
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'tahseel_new',
            'database.connections.mysql.username' => 'tahseelusr',
            'database.connections.mysql.password' => 'YourStrongPassword123!',
            'zernio.driver' => 'zernio',
            'zernio.sandbox' => false,
            'zernio.api_key' => 'sk_test_key',
            'zernio.account_id' => 'acct_test',
            'zernio.base_url' => 'https://zernio.com/api/v1',
            'zernio.waba_id' => 'waba_test',
            'zernio.receipt_template' => 'payment_receipt_v4',
            'zernio.reminder_template' => 'monthly_reminder_v1',
            'zernio.monthly_reminder_enabled' => true,
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
        $this->beginTahseelTransaction();

        $limiter = \Mockery::mock(\App\Services\WhatsApp\WhatsAppRateLimiter::class);
        $limiter->shouldReceive('waitBeforeSend')->andReturn(['allowed' => true, 'waited_seconds' => 0]);
        $this->app->instance(\App\Services\WhatsApp\WhatsAppRateLimiter::class, $limiter);

        // CRITICAL SAFETY: stub the dispatcher — nothing may enqueue a real job.
        $dispatcher = \Mockery::mock(WhatsAppMessageDispatcher::class);
        $dispatcher->shouldReceive('dispatch')->andReturnNull();
        $this->app->instance(WhatsAppMessageDispatcher::class, $dispatcher);
    }

    protected function tearDown(): void
    {
        $this->rollbackTahseelTransaction();
        \Mockery::close();
        parent::tearDown();
    }

    private function seedClient(string $phone): int
    {
        return DB::table('tbl_clients')->insertGetId([
            'name' => 'PhoneValidation Test '.uniqid(),
            'phone' => $phone,
            'subscription_id' => 0,
            'subscription_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedPaidInvoice(int $clientId): int
    {
        \App\Models\Admin\Invoice::unguard();
        $invoiceId = DB::table('tbl_invoices')->insertGetId([
            'invoice_number' => 'PV-TEST-'.uniqid(),
            'client_id' => $clientId,
            'subscription_id' => 0,
            'amount' => 25,
            'paid_amount' => 25,
            'remaining_amount' => 0,
            'enshaa_date' => now()->toDateString(),
            'due_date' => '2026-07-15',
            'paid_date' => now()->toDateString(),
            'status' => 'paid',
            'invoice_type' => 'service',
            'notes' => 'باقي حساب راوتر',
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) $invoiceId;
    }

    public function test_receipt_with_garbage_phone_creates_failed_row_without_dispatch(): void
    {
        $dispatcher = \Mockery::mock(WhatsAppMessageDispatcher::class);
        $dispatcher->shouldNotReceive('dispatch');
        $this->app->instance(WhatsAppMessageDispatcher::class, $dispatcher);

        $clientId = $this->seedClient('961000000'); // the reported garbage number
        $invoiceId = $this->seedPaidInvoice($clientId);
        $invoice = \App\Models\Admin\Invoice::find($invoiceId);

        $result = app(PaymentReceiptNotifier::class)->notify($invoice);

        $this->assertSame('not_applicable', $result);

        $row = WhatsAppMessageLog::query()
            ->where('client_id', $clientId)
            ->where('template_type', 'receipt')
            ->first();

        $this->assertNotNull($row, 'A visible failed receipt row must exist so admins see WHY nothing was sent');
        $this->assertSame('failed', $row->status);
        $this->assertStringContainsString('Invalid phone number', (string) $row->error);
        $this->assertStringContainsString('961000000', (string) $row->error);
    }

    public function test_receipt_with_valid_phone_is_queued_and_normalized(): void
    {
        $dispatcher = \Mockery::mock(WhatsAppMessageDispatcher::class);
        $dispatcher->shouldReceive('dispatch')->once()->andReturnNull();
        $this->app->instance(WhatsAppMessageDispatcher::class, $dispatcher);

        $clientId = $this->seedClient('70123456');
        $invoiceId = $this->seedPaidInvoice($clientId);
        $invoice = \App\Models\Admin\Invoice::find($invoiceId);

        $result = app(PaymentReceiptNotifier::class)->notify($invoice);

        $this->assertSame('queued', $result);

        $row = WhatsAppMessageLog::query()
            ->where('client_id', $clientId)
            ->where('template_type', 'receipt')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('pending', $row->status);
        $this->assertSame('+96170123456', $row->phone, 'Valid phone must be stored normalized to E.164');
    }

    public function test_monthly_reminder_with_invalid_phone_is_skipped_without_row(): void
    {
        $clientId = $this->seedClient('961000000');

        $result = app(MonthlyReminderNotifier::class)->notify($clientId);

        $this->assertSame('not_applicable', $result);

        $exists = WhatsAppMessageLog::query()->where('client_id', $clientId)->exists();
        $this->assertFalse($exists, 'Monthly reminder must not create any row for an invalid phone');
    }

    public function test_delivery_backstop_marks_invalid_row_failed(): void
    {
        // Simulate a legacy queued row with a garbage phone (bypassed entry gates).
        \App\Models\Admin\Invoice::unguard();
        $logId = WhatsAppMessageLog::query()->create([
            'client_id' => null,
            'client_name' => 'Legacy Row',
            'phone' => '961000000',
            'message' => 'legacy message body',
            'template_type' => 'collector_reminder',
            'status' => 'pending',
            'error' => null,
            'sent_by' => 'test:backstop',
        ])->id;

        $job = new \App\Jobs\SendWhatsAppMessage($logId);
        $service = \Mockery::mock(\App\Services\WhatsAppService::class);
        $service->shouldReceive('status')->andReturn(['connected' => true]);
        $service->shouldNotReceive('send');

        $job->handle($service);

        $row = WhatsAppMessageLog::query()->find($logId);
        $this->assertSame('failed', $row->status);
        $this->assertStringContainsString('Invalid phone number', (string) $row->error);
        $this->assertStringContainsString('delivery time', (string) $row->error);
    }
}
