<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\WhatsAppSettingsController;
use App\Models\WhatsAppMessageLog;
use App\Models\Admin\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

/**
 * Spec 018 (Button 2): the Settings -> WhatsApp -> Monthly Reminders card
 * (sendMonthly) must route through MonthlyReminderNotifier when the Zernio
 * driver + reminder template are configured, producing a monthly_reminder log.
 */
class MonthlyReminderSettingsCardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run against the real MySQL database (mirrors MonthlyReminderNotifierTest)
        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=tahseel_new');
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'tahseel_new']);

        DB::table('app_config')->updateOrInsert(
            ['key' => 'whatsapp_enabled'],
            ['value' => '1', 'updated_at' => now()]
        );

        config([
            'zernio.driver' => 'zernio',
            'zernio.reminder_template' => 'monthly_reminder_v1',
            'zernio.sandbox' => false,
        ]);

        // Prevent any real transport — the controller calls whatsapp->send directly.
        $mock = Mockery::mock(WhatsAppService::class);
        $mock->shouldReceive('status')->andReturn(['connected' => true]);
        $mock->shouldReceive('send')->andReturn(['success' => true, 'error' => null]);
        $this->app->instance(WhatsAppService::class, $mock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function seedClientWithInvoices(): int
    {
        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'SettingsCard Test ' . uniqid(),
            'phone' => '+96170781562',
            'subscription_id' => 0,
            'subscription_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Invoice::unguard();
        $mk = function (array $a) use ($clientId) {
            static $seq = 0;
            $seq++;
            return array_merge([
                'invoice_number' => 'SC-TEST-' . uniqid() . '-' . $seq,
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

        Invoice::insert([
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
        ]);

        return $clientId;
    }

    public function test_send_monthly_uses_meta_template_when_configured(): void
    {
        $clientId = $this->seedClientWithInvoices();

        // Scope sendMonthly to only our seeded client by overriding the month query set.
        // We patch the controller's invoice query indirectly: sendMonthly sends to ALL
        // clients with unpaid invoices in the month, so we assert that the seeded client
        // received a monthly_reminder log (not a free-text 'reminder' log).
        $controller = app(WhatsAppSettingsController::class);
        $response = $controller->sendMonthly(new Request(['month' => 8, 'year' => 2026]));

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success'] ?? false, 'sendMonthly should report success');

        $log = WhatsAppMessageLog::where('client_id', $clientId)
            ->where('template_type', 'monthly_reminder')
            ->first();

        $this->assertNotNull($log, 'sendMonthly must enqueue a monthly_reminder template log');
        $this->assertCount(5, $log->template_variables, 'monthly_reminder_v1 expects 5 variables');

        // Cleanup
        WhatsAppMessageLog::where('client_id', $clientId)->delete();
        DB::table('tbl_invoices')->where('client_id', $clientId)->delete();
        DB::table('tbl_clients')->where('id', $clientId)->delete();
    }
}
