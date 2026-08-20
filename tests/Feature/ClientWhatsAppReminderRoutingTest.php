<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\WhatsAppSettingsController;
use App\Models\Admin\Invoice;
use App\Models\Clients;
use App\Services\WhatsApp\MonthlyReminderNotifier;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

/**
 * Spec 018 (Option A): the "إرسال تذكير واتساب" (Send WhatsApp reminder) button
 * on the clients page must route through MonthlyReminderNotifier (approved
 * monthly_reminder_v1 template) when the Zernio driver + reminder template are
 * configured — instead of the legacy free-text send.
 *
 * SAFETY: MonthlyReminderNotifier is MOCKED so this test can NEVER enqueue a real
 * job or trigger a real send, even against the live DB.
 */
class ClientWhatsAppReminderRoutingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

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
            'zernio.monthly_reminder_enabled' => true,
            'zernio.sandbox' => false,
        ]);

        // The controller checks status() first — stub it as connected.
        $svc = Mockery::mock(WhatsAppService::class);
        $svc->shouldReceive('status')->andReturn(['connected' => true]);
        $this->app->instance(WhatsAppService::class, $svc);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function seedClientWithInvoices(): int
    {
        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'ClientReminder Test ' . uniqid(),
            'phone' => '+96170781562',
            'subscription_id' => 0,
            'subscription_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Invoice::unguard();
        Invoice::insert([
            [
                'invoice_number' => 'CR-TEST-' . uniqid(),
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
            ],
        ]);

        return $clientId;
    }

    public function test_send_client_reminder_routes_to_notifier_when_configured(): void
    {
        $clientId = $this->seedClientWithInvoices();

        // Critical SAFETY: stub the notifier so clicking can never enqueue a real job.
        $notifier = Mockery::mock(MonthlyReminderNotifier::class);
        $notifier->shouldReceive('notify')->with((int) $clientId)->andReturn('queued');
        $this->app->instance(MonthlyReminderNotifier::class, $notifier);

        $controller = app(WhatsAppSettingsController::class);
        $response = $controller->sendClientReminder($clientId, new Request(['filter_due' => 1]));

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success'] ?? false, 'Send reminder should report success');
        $notifier->shouldHaveReceived('notify')->with((int) $clientId)->once();
    }
}
