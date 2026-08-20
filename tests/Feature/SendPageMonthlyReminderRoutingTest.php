<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\WhatsAppControlCenterController;
use App\Models\Admin\Invoice;
use App\Services\WhatsApp\MonthlyReminderNotifier;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

/**
 * Spec 018 (send page): the WhatsApp Control Center "send" page (/whatsapp/send)
 * must be able to send via the APPROVED monthly_reminder_v1 Meta template by
 * selecting the "Monthly Reminder" template type, which routes each client
 * through MonthlyReminderNotifier (5 vars) instead of free-text.
 *
 * SAFETY: MonthlyReminderNotifier and WhatsAppService are mocked — this test can
 * never enqueue a real job or fire a real send.
 */
class SendPageMonthlyReminderRoutingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=tahseel_new');
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'tahseel_new']);

        config([
            'zernio.driver' => 'zernio',
            'zernio.reminder_template' => 'monthly_reminder_v1',
            'zernio.monthly_reminder_enabled' => true,
            'zernio.sandbox' => false,
        ]);

        // Prevent real transport
        $svc = Mockery::mock(WhatsAppService::class);
        $svc->shouldReceive('status')->andReturn(['connected' => true]);
        $svc->shouldReceive('send')->andReturn(['success' => true, 'error' => null]);
        $this->app->instance(WhatsAppService::class, $svc);

        // SAFETY: stub notifier so sending can never enqueue a real job.
        $notifier = Mockery::mock(MonthlyReminderNotifier::class);
        $notifier->shouldReceive('notify')->andReturn('queued');
        $this->app->instance(MonthlyReminderNotifier::class, $notifier);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_broadcast_with_monthly_reminder_routes_through_notifier(): void
    {
        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'SendPage MR ' . uniqid(),
            'phone' => '+96170781562',
            'subscription_id' => 0,
            'subscription_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Invoice::unguard();
        Invoice::insert([
            'invoice_number' => 'SP-MR-' . uniqid(),
            'client_id' => $clientId,
            'subscription_id' => 0,
            'amount' => 30.00,
            'enshaa_date' => now()->toDateString(),
            'remaining_amount' => 30.00,
            'due_date' => '2026-08-15',
            'status' => 'unpaid',
            'notes' => null,
            'invoice_type' => 'subscription',
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = app(WhatsAppControlCenterController::class);
        $response = $controller->broadcast(new Request([
            'template_type' => 'monthly_reminder',
            'client_ids' => [$clientId],
        ]));

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success'] ?? false, 'broadcast should report success');
        $this->assertEquals(1, $data['queued'] ?? 0, 'should queue 1 via notifier');
        $this->app->make(MonthlyReminderNotifier::class)->shouldHaveReceived('notify')->once();
    }

    public function test_monthly_reminder_is_a_known_template_type(): void
    {
        $templates = \App\Services\WhatsApp\WhatsAppTemplateService::getAll();
        $this->assertArrayHasKey('monthly_reminder', $templates, 'send page should offer Monthly Reminder');
    }
}
