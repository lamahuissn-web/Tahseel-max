<?php

namespace Tests\Feature;

use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\MonthlyReminderNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

/**
 * Tests for Spec 018 — Monthly Reminder Template Wiring (monthly_reminder_v1).
 *
 * Validates that MonthlyReminderNotifier builds the 5 Meta template variables,
 * stores them on the log row with template_type='monthly_reminder', and that
 * SendWhatsAppMessage routes that type to the configured reminder template.
 */
class MonthlyReminderNotifierTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use real MariaDB (matches ZernioEndToEndTest pattern)
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
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        // The rate limiter is always-enabled (settings() hardcodes enabled=true) and the
        // dev DB has hundreds of real 'sent' logs in the last hour, so the hourly cap
        // blocks test sends. Mock it for this test only — no production behavior change.
        $limiter = \Mockery::mock(\App\Services\WhatsApp\WhatsAppRateLimiter::class);
        $limiter->shouldReceive('waitBeforeSend')->andReturn(['allowed' => true, 'waited_seconds' => 0]);
        $this->app->instance(\App\Services\WhatsApp\WhatsAppRateLimiter::class, $limiter);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    private function seedClientWithInvoices(): int
    {
        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'MonthlyReminder Test ' . uniqid(),
            'phone' => '+96170781562',
            'subscription_id' => 0,
            'subscription_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \App\Models\Admin\Invoice::unguard();
        $mk = function (array $a) use ($clientId) {
            static $seq = 0;
            $seq++;
            return array_merge([
                'invoice_number' => 'MR-TEST-' . uniqid() . '-' . $seq,
                'client_id' => $clientId,
                'subscription_id' => 0,
                'amount' => 0,
                'enshaa_date' => now()->toDateString(),
                'remaining_amount' => 0,
                'due_date' => null,
                'status' => 'unpaid',
                'notes' => null,
                'invoice_type' => 'subscription',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], $a);
        };

        \App\Models\Admin\Invoice::insert([
            $mk([
                'invoice_type' => 'subscription',
                'status' => 'unpaid',
                'amount' => 25.00,
                'remaining_amount' => 25.00,
                'due_date' => '2026-07-15',
            ]),
            $mk([
                'invoice_type' => 'subscription',
                'status' => 'unpaid',
                'amount' => 25.00,
                'remaining_amount' => 25.00,
                'due_date' => '2026-08-15',
            ]),
            $mk([
                'invoice_type' => 'service',
                'status' => 'unpaid',
                'amount' => 10.00,
                'remaining_amount' => 10.00,
                'due_date' => '2026-06-10',
                'notes' => 'باقي حساب راوتر',
            ]),
            // Soft-deleted unpaid — MUST be excluded (deleted_at IS NULL rule)
            $mk([
                'invoice_type' => 'subscription',
                'status' => 'unpaid',
                'amount' => 99.00,
                'remaining_amount' => 99.00,
                'due_date' => '2026-05-10',
                'deleted_at' => now(),
            ]),
        ]);

        return $clientId;
    }

    public function test_notifier_builds_five_variables_and_logs_monthly_reminder(): void
    {
        $clientId = $this->seedClientWithInvoices();

        $notifier = app(MonthlyReminderNotifier::class);
        $result = $notifier->notify($clientId);

        $this->assertEquals('queued', $result);

        $log = WhatsAppMessageLog::where('client_id', $clientId)
            ->where('template_type', 'monthly_reminder')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'A monthly_reminder log row was created');
        $this->assertNotNull($log->template_variables, 'template_variables populated');

        $vars = $log->template_variables;
        $this->assertCount(5, $vars, 'Meta template expects exactly 5 variables');

        // {{1}} subscriber name
        $client = DB::table('tbl_clients')->find($clientId);
        $this->assertEquals($client->name, $vars[0]);

        // {{2}} soonest unpaid subscription month as MM/YYYY -> 07/2026
        $this->assertEquals('07/2026', $vars[1], 'Soonest unpaid subscription month');

        // {{3}} unpaid subscription months comma-joined -> "7, 8"
        $this->assertEquals('7, 8', $vars[2], 'Unpaid subscription months list');

        // {{4}} unpaid services desc+amount -> "باقي حساب راوتر ($10.00)"
        $this->assertEquals('باقي حساب راوتر ($10.00)', $vars[3], 'Unpaid services list');

        // {{5}} total outstanding = 25 + 25 + 10 = 60 (deleted 99 excluded)
        $this->assertEquals('60.00', $vars[4], 'Total excludes soft-deleted invoice');

        // Cleanup
        WhatsAppMessageLog::where('client_id', $clientId)->delete();
        DB::table('tbl_invoices')->where('client_id', $clientId)->delete();
        DB::table('tbl_clients')->where('id', $clientId)->delete();
    }

    public function test_job_routes_monthly_reminder_to_configured_template(): void
    {
        // Fake the Zernio inbox send endpoint + number-info (so status() reports connected)
        Http::fake([
            'https://zernio.com/api/v1/inbox/conversations' => Http::response([
                'success' => true,
                'data' => ['messageId' => 'wamid.MR_TEST'],
            ], 201),
            'https://zernio.com/api/v1/whatsapp/number-info*' => Http::response([
                'phone' => ['status' => 'CONNECTED', 'display_phone_number' => '+96170781562'],
                'waba' => ['id' => 'waba_test'],
            ], 200),
        ]);

        $clientId = $this->seedClientWithInvoices();
        $client = DB::table('tbl_clients')->find($clientId);

        // Create the log the way MonthlyReminderNotifier would (template_type + variables),
        // WITHOUT dispatching a second queued job.
        $log = WhatsAppMessageLog::create([
            'client_id' => $clientId,
            'client_name' => $client->name,
            'phone' => $client->phone,
            'message' => 'fallback',
            'template_type' => 'monthly_reminder',
            'template_variables' => [
                $client->name,
                '07/2026',
                '7, 8',
                'باقي حساب راوتر ($10.00)',
                '60.00',
            ],
            'status' => 'pending',
            'sent_by' => 'system:monthly_reminder|batch:test',
        ]);

        // Run the delivery job synchronously (isolates job → template mapping).
        $job = app(\App\Jobs\SendWhatsAppMessage::class, ['messageLogId' => $log->id]);
        $job->handle(app(\App\Services\WhatsAppService::class));

        Http::assertSent(function ($request) {
            $body = $request->data();
            return $request->method() === 'POST'
                && str_contains($request->url(), '/inbox/conversations')
                && ($body['templateName'] ?? null) === 'monthly_reminder_v1'
                && ($body['templateLanguage'] ?? null) === 'ar'
                && is_array($body['templateParams'] ?? null)
                && count($body['templateParams']) === 5;
        });

        // Cleanup
        WhatsAppMessageLog::where('client_id', $clientId)->delete();
        DB::table('tbl_invoices')->where('client_id', $clientId)->delete();
        DB::table('tbl_clients')->where('id', $clientId)->delete();
    }
}
