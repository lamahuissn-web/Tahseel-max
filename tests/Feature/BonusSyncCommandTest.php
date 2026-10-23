<?php

namespace Tests\Feature;

use App\Models\AppConfig;
use App\Models\ClientBonus;
use App\Services\Bonus\BonusSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature 021 — Phase 3 automation.
 *
 * `bonus:sync` may only *record* an entitlement: it writes a `pending` row and
 * never touches an invoice. Everything financial still waits for an admin click.
 */
class BonusSyncCommandTest extends TestCase
{
    private static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! str_contains((string) config('database.connections.'.config('database.default').'.database'), '_test')) {
            throw new \RuntimeException('Refusing migrate:fresh outside a dedicated test database.');
        }

        if (! self::$migrated) {
            Artisan::call('migrate:fresh', ['--force' => true]);
            self::$migrated = true;
        }

        DB::beginTransaction();
        $this->seedSettings();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        BonusSettings::flush();
        parent::tearDown();
    }

    public function test_sync_records_one_pending_bonus_for_an_eligible_customer(): void
    {
        $client     = $this->clientWithMonths(24);
        $revenueBefore = DB::table('tbl_revenues')->where('client_id', $client)->count();
        $invoicesBefore = DB::table('tbl_invoices')->where('client_id', $client)->count();

        $this->artisan('bonus:sync')->assertExitCode(0);

        $bonus = ClientBonus::where('client_id', $client)->first();

        $this->assertNotNull($bonus, 'المستحق يجب أن يُسجَّل تلقائياً');
        $this->assertSame(ClientBonus::STATUS_PENDING, $bonus->status);
        $this->assertSame('auto', $bonus->source);
        $this->assertSame(24, (int) $bonus->cycle_months);

        // recording an entitlement never moves money
        $this->assertSame($invoicesBefore, DB::table('tbl_invoices')->where('client_id', $client)->count());
        $this->assertSame($revenueBefore, DB::table('tbl_revenues')->where('client_id', $client)->count());
    }

    public function test_a_second_run_does_not_duplicate_the_entitlement(): void
    {
        $client = $this->clientWithMonths(24);

        $this->artisan('bonus:sync')->assertExitCode(0);
        $this->artisan('bonus:sync')->assertExitCode(0);

        $this->assertSame(1, ClientBonus::where('client_id', $client)->count());
    }

    public function test_a_customer_below_the_threshold_is_ignored(): void
    {
        $client = $this->clientWithMonths(23);

        $this->artisan('bonus:sync')->assertExitCode(0);

        $this->assertSame(0, ClientBonus::where('client_id', $client)->count());
    }

    public function test_the_master_switch_stops_the_whole_command(): void
    {
        $client = $this->clientWithMonths(24);
        AppConfig::query()->updateOrCreate(['key' => 'bonus_enabled'], ['value' => '0']);
        BonusSettings::flush();

        $this->artisan('bonus:sync')->assertExitCode(0);

        $this->assertSame(0, ClientBonus::where('client_id', $client)->count());
    }

    public function test_inactive_and_satellite_customers_are_skipped(): void
    {
        $inactive  = $this->clientWithMonths(24, 'internet', false);
        $satellite = $this->clientWithMonths(24, 'satellite', true);

        $this->artisan('bonus:sync')->assertExitCode(0);

        $this->assertSame(0, ClientBonus::where('client_id', $inactive)->count());
        $this->assertSame(0, ClientBonus::where('client_id', $satellite)->count());
    }

    public function test_a_customer_with_an_open_bonus_is_not_touched_again(): void
    {
        $client = $this->clientWithMonths(24);

        $this->artisan('bonus:sync')->assertExitCode(0);
        ClientBonus::where('client_id', $client)->update(['status' => ClientBonus::STATUS_POSTPONED]);

        $this->artisan('bonus:sync')->assertExitCode(0);

        $this->assertSame(1, ClientBonus::where('client_id', $client)->count());
        $this->assertSame(ClientBonus::STATUS_POSTPONED, ClientBonus::where('client_id', $client)->value('status'));
    }

    public function test_dry_run_writes_nothing(): void
    {
        $client = $this->clientWithMonths(24);

        $this->artisan('bonus:sync', ['--dry-run' => true])->assertExitCode(0);

        $this->assertSame(0, ClientBonus::where('client_id', $client)->count());
    }

    // ------------------------------------------------------------------ helpers

    private function clientWithMonths(int $months, string $type = 'internet', bool $active = true): int
    {
        $clientId = $this->client($type, $active);
        $dueDates = $this->pastDueDates($months);
        $this->setSetting('bonus_count_start_date', $dueDates[0]);

        foreach ($dueDates as $due) {
            $invoiceId = $this->invoice($clientId, $due);
            $this->pay($invoiceId, $due);
        }

        return $clientId;
    }

    private function pastDueDates(int $n): array
    {
        $dates = [];
        for ($i = $n; $i >= 1; $i--) {
            $dates[] = \Carbon\Carbon::today()->startOfMonth()->subMonthsNoOverflow($i)->day(5)->toDateString();
        }

        return $dates;
    }

    private function seedSettings(): void
    {
        $defaults = [
            'bonus_enabled'                 => '1',
            'bonus_months_required'         => '24',
            'bonus_grace_days'              => '0',
            'bonus_catchup_per_cycle'       => '1',
            'bonus_hard_reset_after_months' => '2',
            'bonus_spiral_after_months'     => '2',
            'bonus_count_start_date'        => '2026-01-01',
            'bonus_require_zero_balance'    => '1',
            'bonus_client_types'            => 'internet',
        ];

        foreach ($defaults as $key => $value) {
            AppConfig::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        BonusSettings::flush();
    }

    private function setSetting(string $key, string $value): void
    {
        AppConfig::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        BonusSettings::flush();
    }

    private function client(string $type = 'internet', bool $active = true): int
    {
        return DB::table('tbl_clients')->insertGetId([
            'name'              => 'عميل مزامنة',
            'phone'             => '0555'.random_int(100000, 999999),
            'address1'          => 'عنوان',
            'client_type'       => $type,
            'subscription_id'   => 1,
            'price'             => 15,
            'subscription_date' => '2026-01-01',
            'start_date'        => '2026-01-01',
            'is_active'         => $active ? '1' : '0',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    private function invoice(int $clientId, string $dueDate): int
    {
        return DB::table('tbl_invoices')->insertGetId([
            'invoice_number'   => 'SINV-'.strtoupper(uniqid()),
            'client_id'        => $clientId,
            'subscription_id'  => 1,
            'amount'           => '15.00',
            'paid_amount'      => '0.00',
            'remaining_amount' => '15.00',
            'enshaa_date'      => $dueDate,
            'due_date'         => $dueDate,
            'status'           => 'unpaid',
            'invoice_type'     => 'subscription',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    private function pay(int $invoiceId, string $at): void
    {
        DB::table('tbl_invoices')->where('id', $invoiceId)->update([
            'paid_amount'      => '15.00',
            'remaining_amount' => 0,
            'status'           => 'paid',
            'paid_date'        => $at.' 10:00:00',
            'updated_at'       => now(),
        ]);

        DB::table('tbl_revenues')->insert([
            'client_id'    => DB::table('tbl_invoices')->where('id', $invoiceId)->value('client_id'),
            'invoice_id'   => $invoiceId,
            'amount'       => '15.00',
            'collected_by' => 1,
            'created_at'   => $at.' 10:00:00',
            'updated_at' => now(),
        ]);
    }
}
