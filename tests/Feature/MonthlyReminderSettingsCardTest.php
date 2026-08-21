<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\WhatsAppSettingsController;
use App\Models\Admin\Invoice;
use App\Models\Clients;
use App\Services\WhatsApp\MonthlyReminderNotifier;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Tests\Traits\WrapsMysqlTransaction;

/**
 * Spec 018 (Button 2): the Settings -> WhatsApp -> Monthly Reminders card
 * (sendMonthly) must route through MonthlyReminderNotifier when the Zernio
 * driver + reminder template are configured.
 *
 * SAFETY: MonthlyReminderNotifier is MOCKED so this test can NEVER enqueue a
 * real WhatsApp job or trigger a real send, even when run against the live DB.
 */
class MonthlyReminderSettingsCardTest extends TestCase
{
    use WrapsMysqlTransaction;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=tahseel_new');
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'tahseel_new']);
        $this->beginTahseelTransaction();

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
        $svc = Mockery::mock(WhatsAppService::class);
        $svc->shouldReceive('status')->andReturn(['connected' => true]);
        $svc->shouldReceive('send')->andReturn(['success' => true, 'error' => null]);
        $this->app->instance(WhatsAppService::class, $svc);

        // CRITICAL SAFETY: stub the notifier so calling sendMonthly can never
        // enqueue a real job or reach WhatsApp. We only assert that the card
        // ROUTES to the notifier — no real message is ever produced.
        $notifier = Mockery::mock(MonthlyReminderNotifier::class);
        $notifier->shouldReceive('notify')->andReturn('queued');
        $this->app->instance(MonthlyReminderNotifier::class, $notifier);
    }

    protected function tearDown(): void
    {
        $this->rollbackTahseelTransaction();
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
        $mk = function () use ($clientId) {
            static $seq = 0;
            $seq++;
            return [
                'invoice_number' => 'SC-TEST-' . uniqid() . '-' . $seq,
                'client_id' => $clientId,
                'subscription_id' => 0,
                'amount' => 25.00,
                'enshaa_date' => now()->toDateString(),
                'remaining_amount' => 25.00,
                'due_date' => '2026-08-15',
                'status' => 'unpaid',
                'notes' => null,
                'invoice_type' => 'subscription',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        };

        Invoice::insert([$mk()]);

        return $clientId;
    }

    public function test_send_monthly_routes_to_notifier_when_configured(): void
    {
        $clientId = $this->seedClientWithInvoices();

        // sendMonthly iterates every unpaid client in the month, so the stub must
        // accept ANY client id and still never produce a real send.
        $notifier = Mockery::mock(MonthlyReminderNotifier::class);
        $notifier->shouldReceive('notify')->andReturn('queued');
        $this->app->instance(MonthlyReminderNotifier::class, $notifier);

        $controller = app(WhatsAppSettingsController::class);
        $response = $controller->sendMonthly(new Request(['month' => 8, 'year' => 2026]));

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['success'] ?? false, 'sendMonthly should report success');

        // The notifier must be consulted (routing proven) — nothing real is sent.
        $notifier->shouldHaveReceived('notify');
        $this->assertNotNull(DB::table('tbl_clients')->find($clientId), 'Seeded client still present');
    }
}
