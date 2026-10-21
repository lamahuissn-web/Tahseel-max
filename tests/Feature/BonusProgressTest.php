<?php

namespace Tests\Feature;

use App\Models\AppConfig;
use App\Services\Bonus\BonusProgressService;
use App\Services\Bonus\BonusSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature 021 — loyalty bonus progress.
 *
 * Rules locked with the owner on 2026-10-21:
 *  - a month counts when it is paid inside its own month (before the next bill),
 *    plus an admin-editable grace in days;
 *  - one catch-up (استدراك) per cycle for a 1–2 month break settled together;
 *  - a break longer than 2 months zeroes the run;
 *  - oldest-first: paying the newest bill while an older one is open earns
 *    nothing (the «دوامة» customer);
 *  - subscriptions only, nothing before the count-start date.
 *
 * Real customer shapes used as fixtures: جمال يعقوب (#518) pays a few days late
 * but always inside the month; عامر يعقوب (#1576) lets a month roll over.
 */
class BonusProgressTest extends TestCase
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

    // ----------------------------------------------------------- the happy path

    public function test_twenty_four_on_time_months_earn_one_free_month(): void
    {
        $client = $this->client();
        $dueDates = $this->pastDueDates(24);
        $this->setSetting('bonus_count_start_date', $dueDates[0]);

        foreach ($dueDates as $due) {
            $inv = $this->invoice($client, $due);
            $this->pay($inv, $client, $due);
        }

        $progress = $this->service()->forClient($client);

        $this->assertSame(24, $progress->progress);
        $this->assertTrue($progress->eligible, '24 on-time months must make the customer eligible.');
        $this->assertSame('compliant', $progress->status);
    }

    public function test_progress_is_capped_at_the_required_months(): void
    {
        $client = $this->client();
        $dueDates = $this->pastDueDates(30);
        $this->setSetting('bonus_count_start_date', $dueDates[0]);

        foreach ($dueDates as $due) {
            $inv = $this->invoice($client, $due);
            $this->pay($inv, $client, $due);
        }

        $progress = $this->service()->forClient($client);

        $this->assertSame(24, $progress->progress, 'Progress never exceeds 24.');
    }

    // ---------------------------------------------------------------- the month

    public function test_paid_a_few_days_after_due_but_inside_the_month_counts(): void
    {
        // Real case: جمال يعقوب paid 9 days late but before the next bill.
        $client = $this->client();

        $march   = $this->invoice($client, '2026-03-02');
        $april   = $this->invoice($client, '2026-04-02');
        $this->pay($march, $client, '2026-03-11');
        $this->pay($april, $client, '2026-04-04');

        $progress = $this->service()->forClient($client);

        $this->assertTrue($progress->months[0]['credited'], 'A late-but-inside-the-month payment must count.');
        $this->assertSame(2, $progress->progress);
    }

    public function test_a_month_still_unpaid_when_the_next_bill_arrives_is_not_credited(): void
    {
        // Real case: عامر يعقوب — August paid, September left open.
        $client = $this->client();

        $aug = $this->invoice($client, '2026-08-16');
        $this->invoice($client, '2026-09-16');
        $this->invoice($client, '2026-10-16');
        $this->pay($aug, $client, '2026-08-16');

        $progress = $this->service()->forClient($client);

        $this->assertSame(1, $progress->progress);
        $this->assertSame('on_time', $progress->months[0]['state']);
        $this->assertSame('unpaid', $progress->months[1]['state']);
        $this->assertFalse($progress->months[1]['credited']);
    }

    public function test_grace_days_are_read_live_from_settings(): void
    {
        $client = $this->client();
        $jan = $this->invoice($client, '2026-01-10');
        $this->invoice($client, '2026-02-10');
        $this->pay($jan, $client, '2026-02-12');   // 2 days past the next bill

        $strict = $this->service()->forClient($client);
        $this->assertFalse($strict->months[0]['credited'], 'Without grace the month is not credited.');

        $this->setSetting('bonus_grace_days', '7');

        $relaxed = $this->service()->forClient($client);
        $this->assertTrue($relaxed->months[0]['credited'], 'Grace days must be applied from settings.');
    }

    public function test_service_invoices_never_count(): void
    {
        $client = $this->client();
        $this->invoice($client, '2026-01-10', ['invoice_type' => 'service']);

        $progress = $this->service()->forClient($client);

        $this->assertSame(0, $progress->progress);
        $this->assertSame([], $progress->months);
    }

    public function test_nothing_before_the_count_start_date_counts(): void
    {
        $client = $this->client();
        $old = $this->invoice($client, '2025-11-10');
        $this->pay($old, $client, '2025-11-10');

        $progress = $this->service()->forClient($client);

        $this->assertSame(0, $progress->progress, 'History before 2026-01-01 is ignored.');
    }

    // -------------------------------------------------------------- catch-up

    public function test_catch_up_credits_both_months_when_settled_together(): void
    {
        $client = $this->client();

        $jul = $this->invoice($client, '2026-07-16');
        $aug = $this->invoice($client, '2026-08-16');
        $sep = $this->invoice($client, '2026-09-16');
        $this->invoice($client, '2026-10-16');

        $this->pay($jul, $client, '2026-07-16');
        // August + September paid together on 2026-09-20, before October's bill.
        $this->pay($aug, $client, '2026-09-20');
        $this->pay($sep, $client, '2026-09-20');

        $progress = $this->service()->forClient($client);

        $this->assertTrue($progress->months[1]['credited'], 'The broken month is credited via catch-up.');
        $this->assertTrue($progress->months[2]['credited'], 'The current month is credited too.');
        $this->assertSame(1, $progress->catchupsUsed);
        $this->assertSame(3, $progress->progress);
    }

    public function test_catch_up_is_limited_to_one_per_cycle(): void
    {
        $client = $this->client();

        $jan = $this->invoice($client, '2026-01-16');
        $feb = $this->invoice($client, '2026-02-16');
        $jun = $this->invoice($client, '2026-06-16');
        $jul = $this->invoice($client, '2026-07-16');
        $this->invoice($client, '2026-08-16');

        $this->pay($jan, $client, '2026-01-16');
        $this->pay($feb, $client, '2026-02-20');   // first catch-up (used)
        $this->pay($jun, $client, '2026-07-20');   // second break — allowance already spent
        $this->pay($jul, $client, '2026-07-20');

        $progress = $this->service()->forClient($client);

        $this->assertSame(1, $progress->catchupsUsed);
    }

    // ------------------------------------------------------------ hard reset

    public function test_breaking_more_than_two_months_zeroes_the_run(): void
    {
        $client = $this->client();

        $jan = $this->invoice($client, '2026-01-16');
        $this->invoice($client, '2026-02-16');
        $this->invoice($client, '2026-03-16');
        $this->invoice($client, '2026-04-16');
        $this->invoice($client, '2026-05-16');   // third consecutive open month
        $this->pay($jan, $client, '2026-01-16');

        $progress = $this->service()->forClient($client);

        $this->assertSame(0, $progress->progress, 'Three consecutive open months wipe the earned run.');
        $this->assertSame('hard_reset', $progress->status);
    }

    // ------------------------------------------------------------ the spin trap

    public function test_paying_the_newest_month_while_an_older_one_is_open_credits_nothing(): void
    {
        // Real case: رواد موسي (#702) — every new month paid, the oldest never settled.
        $client = $this->client();

        $this->invoice($client, '2026-06-16');          // stays open forever
        foreach (['2026-07-16', '2026-08-16', '2026-09-16', '2026-10-16'] as $due) {
            $inv = $this->invoice($client, $due);
            $this->pay($inv, $client, $due);            // always pays the newest bill
        }

        $progress = $this->service()->forClient($client);

        $this->assertSame(
            0,
            collect($progress->months)->where('credited', true)->count(),
            'Nothing is credited while the account never reaches zero.'
        );
        $this->assertSame(0, $progress->progress);
    }

    public function test_clearing_the_arrears_restores_crediting_from_the_next_month(): void
    {
        $client = $this->client();
        $dates = $this->pastDueDates(4);
        $this->setSetting('bonus_count_start_date', $dates[0]);

        $aug = $this->invoice($client, $dates[0]);
        $sep = $this->invoice($client, $dates[1]);
        $oct = $this->invoice($client, $dates[2]);
        $nov = $this->invoice($client, $dates[3]);

        // The two arrears are settled together just before October's bill.
        $clearAt = \Carbon\Carbon::parse($dates[2])->subDays(6)->toDateString();
        $this->pay($aug, $client, $clearAt);
        $this->pay($sep, $client, $clearAt);
        $this->pay($oct, $client, \Carbon\Carbon::parse($dates[2])->addDays(2)->toDateString());
        $this->pay($nov, $client, $dates[3]);

        $progress = $this->service()->forClient($client);

        $this->assertTrue($progress->months[2]['credited'], 'October counts once the arrears are clear.');
        $this->assertTrue($progress->months[3]['credited'], 'November counts too.');
        $this->assertSame('catchup_used', $progress->status, 'He used his one catch-up this cycle.');
    }

    public function test_carried_arrears_on_two_boundaries_puts_the_customer_in_spin_state(): void
    {
        $client = $this->client();

        $this->invoice($client, '2026-06-16');          // oldest, never paid
        foreach (['2026-07-16', '2026-08-16', '2026-09-16', '2026-10-16'] as $due) {
            $inv = $this->invoice($client, $due);
            $this->pay($inv, $client, $due);
        }

        $progress = $this->service()->forClient($client);

        $this->assertGreaterThanOrEqual(2, $progress->carriedArrears);
        $this->assertSame('spiral', $progress->status);
        $this->assertSame(0, $progress->progress);
        $this->assertFalse($progress->eligible);
    }

    // --------------------------------------------------------------- the bonus

    public function test_a_redeemed_bonus_invoice_is_excluded_from_counting(): void
    {
        $client = $this->client();
        $freeInvoice = $this->invoice($client, '2026-03-16', [
            'amount' => '0.00', 'paid_amount' => '0.00', 'remaining_amount' => '0.00', 'status' => 'paid',
        ]);

        DB::table('tbl_client_bonuses')->insert([
            'client_id' => $client, 'earned_at' => '2026-02-16', 'status' => 'redeemed',
            'source' => 'auto', 'redeemed_at' => '2026-03-01 10:00:00',
            'redeemed_invoice_id' => $freeInvoice, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $jan = $this->invoice($client, '2026-01-16');
        $this->pay($jan, $client, '2026-01-16');

        $progress = $this->service()->forClient($client);

        $months = array_column($progress->months, 'month');
        $this->assertNotContains('2026-03', $months, 'The free month is not part of the count.');
    }

    public function test_the_streak_restarts_after_a_redemption(): void
    {
        $client = $this->client();

        $jan = $this->invoice($client, '2026-01-16');
        $feb = $this->invoice($client, '2026-02-16');
        $this->pay($jan, $client, '2026-01-16');
        $this->pay($feb, $client, '2026-02-16');

        DB::table('tbl_client_bonuses')->insert([
            'client_id' => $client, 'earned_at' => '2026-02-16', 'status' => 'redeemed',
            'source' => 'auto', 'redeemed_at' => '2026-02-20 10:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $sep = $this->invoice($client, '2026-09-16');
        $this->pay($sep, $client, '2026-09-16');

        $progress = $this->service()->forClient($client);

        $this->assertSame(1, $progress->progress, 'Only months after the redemption count.');
        $this->assertSame('2026-09', $progress->months[0]['month']);
    }

    public function test_an_open_bonus_blocks_eligibility(): void
    {
        $client = $this->client();
        $dueDates = $this->pastDueDates(24);
        $this->setSetting('bonus_count_start_date', $dueDates[0]);

        foreach ($dueDates as $due) {
            $inv = $this->invoice($client, $due);
            $this->pay($inv, $client, $due);
        }

        DB::table('tbl_client_bonuses')->insert([
            'client_id' => $client, 'earned_at' => now()->toDateString(), 'status' => 'pending',
            'source' => 'auto', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $progress = $this->service()->forClient($client);

        $this->assertTrue($progress->hasOpenBonus);
        $this->assertFalse($progress->eligible, 'Only one un-redeemed bonus at a time.');
    }

    // ------------------------------------------------------------- performance

    public function test_the_list_path_does_not_scale_queries_per_client(): void
    {
        $service = $this->service();

        $small = [];
        for ($c = 0; $c < 2; $c++) {
            $id = $this->client();
            $inv = $this->invoice($id, '2026-01-16');
            $this->pay($inv, $id, '2026-01-16');
            $small[] = $id;
        }

        // Warm the settings memo so the measurement is not skewed by the one
        // settings read that happens on the very first call.
        BonusSettings::all();

        DB::enableQueryLog();
        $service->forClients($small);
        $smallQueries = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        $big = $small;
        for ($c = 0; $c < 8; $c++) {
            $id = $this->client();
            $inv = $this->invoice($id, '2026-01-16');
            $this->pay($inv, $id, '2026-01-16');
            $big[] = $id;
        }

        DB::enableQueryLog();
        $service->forClients($big);
        $bigQueries = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        $this->assertSame($smallQueries, $bigQueries, 'Query count must not grow with the number of clients.');
    }

    public function test_the_spiral_list_only_returns_customers_with_open_older_months(): void
    {
        // Pays a recent bill but leaves old ones open ⇒ spin.
        $spin = $this->client('زبون دوامة');
        $this->invoice($spin, '2026-06-16');
        $this->invoice($spin, '2026-07-16');
        $recent = $this->invoice($spin, '2026-10-16');
        $this->pay($recent, $spin, '2026-10-05');

        // Fully paid ⇒ never in the list.
        $clean = $this->client('زبون منتظم');
        $inv = $this->invoice($clean, '2026-06-16');
        $this->pay($inv, $clean, '2026-06-16');

        // Two open invoices and no recent payment ⇒ defaulter, not a spin customer.
        $stopped = $this->client('زبون متوقف');
        $this->invoice($stopped, '2026-05-16');
        $this->invoice($stopped, '2026-06-16');

        $rows = collect($this->service()->spiralClients())->pluck('client_id')->all();

        $this->assertContains($spin, $rows);
        $this->assertNotContains($clean, $rows);
        $this->assertNotContains($stopped, $rows);
    }

    public function test_a_bill_that_is_not_due_yet_never_breaks_the_run(): void
    {
        $client = $this->client();
        $inv = $this->invoice($client, '2026-01-16');
        $this->pay($inv, $client, '2026-01-16');

        // Invoice generated a month ahead — it must be ignored, not treated as unpaid.
        $this->invoice($client, now()->addMonthNoOverflow()->toDateString());

        $progress = $this->service()->forClient($client);

        $this->assertSame(1, $progress->progress);
        $this->assertSame('compliant', $progress->status);
        $this->assertCount(1, $progress->months, 'Future months are not part of the evaluation yet.');
    }

    public function test_two_invoices_in_one_month_earn_only_one_credit(): void
    {
        // Real case: اسامة كساب #630 — the billing day moved from the 1st to the
        // 16th in June, so that month has two invoices.
        $client = $this->client();
        $june1  = $this->invoice($client, '2026-06-01');
        $june16 = $this->invoice($client, '2026-06-16');
        $this->pay($june1, $client, '2026-06-01');
        $this->pay($june16, $client, '2026-06-16');

        $progress = $this->service()->forClient($client);

        $this->assertSame(1, $progress->progress, 'A month can earn one point, not two.');
        $this->assertSame('duplicate', $progress->months[1]['state']);
        $this->assertFalse($progress->months[1]['credited']);
    }

    // ------------------------------------------------------ inactive exclusion

    public function test_inactive_customers_are_outside_the_program(): void
    {
        $client = $this->client('زبون موقوف', 'internet', false);

        for ($m = 1; $m <= 6; $m++) {
            $due = \Carbon\Carbon::today()->startOfMonth()->subMonthsNoOverflow(6 - $m)->day(5)->toDateString();
            $inv = $this->invoice($client, $due);
            $this->pay($inv, $client, $due);
        }

        $progress = $this->service()->forClient($client);

        $this->assertSame('inactive', $progress->status, 'A stopped customer is not part of the program.');
        $this->assertSame(0, $progress->progress);
        $this->assertFalse($progress->eligible);
    }

    public function test_reactivating_a_customer_brings_the_count_back(): void
    {
        $client = $this->client('زبون موقوف', 'internet', false);
        $inv = $this->invoice($client, '2026-01-16');
        $this->pay($inv, $client, '2026-01-16');

        $this->assertSame(0, $this->service()->forClient($client)->progress);

        DB::table('tbl_clients')->where('id', $client)->update(['is_active' => '1']);

        $this->assertSame(1, $this->service()->forClient($client)->progress);
    }

    public function test_inactive_customers_never_appear_in_the_spiral_list(): void
    {
        $stopped = $this->client('موقوف مدين', 'internet', false);
        $this->invoice($stopped, '2026-06-16');
        $this->invoice($stopped, '2026-07-16');
        $recent = $this->invoice($stopped, '2026-10-16');
        $this->pay($recent, $stopped, '2026-10-05');

        $rows = collect($this->service()->spiralClients())->pluck('client_id')->all();

        $this->assertNotContains($stopped, $rows);
    }

    // ------------------------------------------------------- satellite exclusion

    public function test_satellite_customers_are_excluded_from_the_program(): void
    {
        $client = $this->client('زبون ستلايت', 'satellite');

        for ($m = 1; $m <= 24; $m++) {
            $due = now()->startOfYear()->addMonthsNoOverflow($m - 1)->day(5)->toDateString();
            $inv = $this->invoice($client, $due);
            $this->pay($inv, $client, $due);
        }

        $progress = $this->service()->forClient($client);

        $this->assertSame('excluded', $progress->status, 'Satellite customers are not part of the program.');
        $this->assertSame(0, $progress->progress);
        $this->assertFalse($progress->eligible);
    }

    public function test_satellite_customers_never_appear_in_the_spiral_list(): void
    {
        $sat = $this->client('ستلايت مدين', 'satellite');
        $this->invoice($sat, '2026-06-16');
        $this->invoice($sat, '2026-07-16');
        $recent = $this->invoice($sat, '2026-10-16');
        $this->pay($recent, $sat, '2026-10-05');

        $rows = collect($this->service()->spiralClients())->pluck('client_id')->all();

        $this->assertNotContains($sat, $rows);
    }

    public function test_including_satellite_in_settings_brings_them_back(): void
    {
        $client = $this->client('ستلايت مشمول', 'satellite');
        $inv = $this->invoice($client, '2026-01-16');
        $this->pay($inv, $client, '2026-01-16');

        $this->setSetting('bonus_client_types', 'internet,satellite');

        $progress = $this->service()->forClient($client);

        $this->assertSame(1, $progress->progress, 'The admin can opt satellite back in from settings.');
    }

    // ---------------------------------------------------------------- helpers

    /** @return array<int,string> n monthly due dates ending last month */
    private function pastDueDates(int $n): array
    {
        $dates = [];
        for ($i = $n; $i >= 1; $i--) {
            $dates[] = \Carbon\Carbon::today()->startOfMonth()->subMonthsNoOverflow($i)->day(5)->toDateString();
        }

        return $dates;
    }

    private function service(): BonusProgressService
    {
        return app(BonusProgressService::class);
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

    private function client(string $name = 'عميل بونص', string $type = 'internet', bool $active = true): int
    {
        return DB::table('tbl_clients')->insertGetId([
            'name'            => $name,
            'phone'           => '0555'.random_int(100000, 999999),
            'address1'        => 'عنوان',
            'client_type'     => $type,
            'subscription_id' => 1,
            'price'           => 15,
            'subscription_date' => '2026-01-01',
            'start_date'      => '2026-01-01',
            'is_active'       => $active ? '1' : '0',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    private function invoice(int $clientId, string $dueDate, array $overrides = []): int
    {
        $data = array_merge([
            'invoice_number'   => 'BINV-'.strtoupper(uniqid()),
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
        ], $overrides);

        return DB::table('tbl_invoices')->insertGetId($data);
    }

    private function pay(int $invoiceId, int $clientId, string $at): void
    {
        $amount = (float) DB::table('tbl_invoices')->where('id', $invoiceId)->value('amount');

        DB::table('tbl_invoices')->where('id', $invoiceId)->update([
            'paid_amount'      => $amount,
            'remaining_amount' => 0,
            'status'           => 'paid',
            'paid_date'        => $at.' 10:00:00',
            'updated_at'       => now(),
        ]);

        DB::table('tbl_revenues')->insert([
            'amount'           => $amount,
            'invoice_id'       => $invoiceId,
            'client_id'        => $clientId,
            'collected_by'     => 1,
            'received_at'      => $at.' 10:00:00',
            'status'           => 'paid',
            'remaining_amount' => 0,
            'created_at'       => $at.' 10:00:00',
            'updated_at'       => $at.' 10:00:00',
        ]);
    }
}
