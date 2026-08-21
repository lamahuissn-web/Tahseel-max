<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Admin\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Testing\Fakes\QueueFake;
use Tests\TestCase;

/**
 * SAFETY AUDIT TESTS (2026-08-21 production-readiness audit).
 *
 * These tests PROVE — empirically, not by assumption — that no automated test can
 * ever dispatch a real WhatsApp message. They reproduce the historical failure modes
 * (test leak via live worker; broken transaction rollback) and hunt a third
 * (direct job push / unmocked service call escaping every mock).
 *
 * Deliberately NO dispatcher/notifier mocks here: these exercise the REAL service
 * code path and assert the global Queue::fake([SendWhatsAppMessage::class]) guard
 * in tests/TestCase.php intercepts every push. If the guard regresses to a no-op
 * (as it once did when passed a CONNECTION name instead of a JOB CLASS), these
 * tests fail loudly.
 *
 * NOTE: no transaction wrapper — the dispatcher defers its push via DB::afterCommit,
 * which would never fire inside an uncommitted transaction. Cleanup is explicit.
 */
class WhatsAppTestSafetyAuditTest extends TestCase
{
    private array $clientIds = [];

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
        ]);
    }

    protected function tearDown(): void
    {
        $this->cleanupSafetyAuditRows();
        parent::tearDown();
    }

    /** GUARD 0: the global fake must actually be installed in every test. */
    public function test_global_queue_fake_is_active(): void
    {
        $this->assertInstanceOf(
            QueueFake::class,
            Queue::getFacadeRoot(),
            'tests/TestCase.php must install Queue::fake([SendWhatsAppMessage::class]) — otherwise tests can reach the real queue'
        );
    }

    /** FAILURE MODE 1: real notifier + real dispatcher, zero mocks. */
    public function test_unmocked_service_call_cannot_reach_real_queue(): void
    {
        $clientId = $this->seedClientWithUnpaidInvoice();

        $result = app(\App\Services\WhatsApp\MonthlyReminderNotifier::class)->notify($clientId);

        $this->assertSame('queued', $result);
        Queue::assertPushed(SendWhatsAppMessage::class);
        $this->assertSame(0, DB::table('jobs')->count(), 'REAL jobs table must stay empty');
        $this->assertSame(0, DB::table('jobs')->where('queue', 'whatsapp')->count());
    }

    /** FAILURE MODE 2: raw job push straight onto the real connection name. */
    public function test_raw_pushon_to_whatsapp_connection_is_intercepted(): void
    {
        Queue::connection('whatsapp_database')->pushOn('whatsapp', new SendWhatsAppMessage(999999));

        Queue::assertPushed(SendWhatsAppMessage::class);
        $this->assertSame(0, DB::table('jobs')->count(), 'REAL jobs table must stay empty');
    }

    /** FAILURE MODE 3: kill switch OFF hard-blocks the service even unmocked. */
    public function test_kill_switch_off_blocks_everything(): void
    {
        config(['zernio.monthly_reminder_enabled' => false]);
        $clientId = $this->seedClientWithUnpaidInvoice();

        $result = app(\App\Services\WhatsApp\MonthlyReminderNotifier::class)->notify($clientId);

        $this->assertSame('disabled', $result);
        Queue::assertNothingPushed();
        $logs = DB::table('whatsapp_message_logs')
            ->where('client_name', 'like', 'SafetyAudit Test%')->count();
        $this->assertSame(0, $logs, 'disabled feature must not create message logs');
        $this->assertSame(0, DB::table('jobs')->count());
    }

    // ------------------------------------------------------------------

    private function seedClientWithUnpaidInvoice(): int
    {
        $clientId = (int) DB::table('tbl_clients')->insertGetId([
            'name' => 'SafetyAudit Test '.uniqid(),
            'phone' => '+96170781562',
            'subscription_id' => 0,
            'subscription_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->clientIds[] = $clientId;

        Invoice::unguard();
        Invoice::insert([
            'invoice_number' => 'SAFETY-'.uniqid(),
            'client_id' => $clientId,
            'subscription_id' => 0,
            'amount' => 10.00,
            'remaining_amount' => 10.00,
            'enshaa_date' => now()->toDateString(),
            'due_date' => '2026-07-15',
            'status' => 'unpaid',
            'notes' => null,
            'invoice_type' => 'service',
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $clientId;
    }

    private function cleanupSafetyAuditRows(): void
    {
        $ids = array_merge(
            $this->clientIds,
            DB::table('tbl_clients')->where('name', 'like', 'SafetyAudit Test%')->pluck('id')->all()
        );
        $ids = array_unique(array_map('intval', $ids));
        if (empty($ids)) {
            return;
        }

        DB::table('whatsapp_message_logs')->whereIn('client_id', $ids)->delete();
        DB::table('tbl_invoices')->whereIn('client_id', $ids)->delete();
        DB::table('tbl_revenues')->whereIn('client_id', $ids)->delete();
        DB::table('tbl_clients')->whereIn('id', $ids)->delete();
        $this->clientIds = [];
    }
}
