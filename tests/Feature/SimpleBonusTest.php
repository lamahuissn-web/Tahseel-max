<?php

namespace Tests\Feature;

use App\Models\ClientBonus;
use App\Services\Bonus\BonusSettings;
use App\Services\Bonus\SimpleBonusProgressService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\BonusDatabase;
use Tests\TestCase;

/**
 * Feature 022 — the simplified bonus.
 *
 * ONE rule: a month is credited iff the customer's TOTAL remaining balance is zero
 * at that month's boundary (due date of the next subscription invoice + grace days),
 * counting every invoice — services included.
 *
 * Cumulative: a bad month is simply not counted. No reset, no spiral, no catch-up,
 * no score, no habit badge.
 */
class SimpleBonusTest extends TestCase
{
    use BonusDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapBonusDatabase();
        BonusSettings::flush();
        $this->seedSettings();
    }

    protected function tearDown(): void
    {
        $this->teardownBonusDatabase();
        BonusSettings::flush();
        parent::tearDown();
    }

    // ------------------------------------------------------------------ rule 1-2

    public function test_a_month_with_a_zero_balance_at_its_boundary_is_credited(): void
    {
        $client = $this->client();
        $dates  = $this->monthlyDues(3);

        foreach ($dates as $d) {
            $this->pay($this->invoice($client, $d), $client, $this->settleDate($d));
        }

        // every boundary is closed and every month was paid inside it
        $this->assertSame(3, $this->service()->forClient($client)->credits);
    }

    public function test_a_month_with_debt_at_its_boundary_is_not_credited_and_nothing_else_is_lost(): void
    {
        $client = $this->client();
        $dates  = $this->monthlyDues(4);

        // month 0 paid, month 1 never paid, months 2-3 paid
        $this->pay($this->invoice($client, $dates[0]), $client, $this->settleDate($dates[0]));
        $this->invoice($client, $dates[1]);
        $this->pay($this->invoice($client, $dates[2]), $client, $this->settleDate($dates[2]));
        $this->pay($this->invoice($client, $dates[3]), $client, $this->settleDate($dates[3]));

        $progress = $this->service()->forClient($client);

        // months 0, 2 and 3 have a zero balance *at their own boundary* only if the
        // unpaid month 1 is settled before them — it is not, so the debt blocks
        // month 1 alone... and everything after it stays blocked until it is paid.
        // Cumulative means the earlier credit is NOT taken back.
        // month 0's boundary is 5 days after month 1 fell due, so month 0 is blocked
        // as well: debt at the boundary is debt, whoever it belongs to.
        $this->assertSame(0, $progress->credits, 'Debt at the boundary blocks the credit — including the earlier month.');
        $this->assertGreaterThan(0.0, $progress->balance, 'The unpaid month stays as a balance.');
    }

    public function test_settling_old_debt_unblocks_the_months_after_it_without_taking_anything_back(): void
    {
        $client = $this->client();
        $dates  = $this->monthlyDues(4);

        $this->pay($this->invoice($client, $dates[0]), $client, $this->settleDate($dates[0]));
        $unpaid = $this->invoice($client, $dates[1]);
        $this->pay($this->invoice($client, $dates[2]), $client, $this->settleDate($dates[2]));
        $this->pay($this->invoice($client, $dates[3]), $client, $this->settleDate($dates[3]));

        $this->assertSame(0, $this->service()->forClient($client)->credits, 'Nothing is credited while the old bill is open.');

        // settle it AFTER month 0's boundary (so month 0 stays uncounted, and is not
        // re-examined) but before the later boundaries — cumulative, never retroactive.
        $this->pay($unpaid, $client, Carbon::parse($dates[1])->addDays(12)->toDateString());

        $this->assertSame(3, $this->service()->forClient($client)->credits, 'Every month that was debt-free at its own boundary counts.');
    }

    // ------------------------------------------------------------------ rule 3

    public function test_service_debt_blocks_the_credit(): void
    {
        $client = $this->client();
        $dates  = $this->monthlyDues(3);

        foreach ($dates as $d) {
            $this->pay($this->invoice($client, $d), $client, $this->settleDate($d));
        }

        // a 40$ service invoice, unpaid, dated inside the first month
        $this->invoice($client, $dates[0], ['invoice_type' => 'service', 'amount' => '40.00']);

        $this->assertSame(0, $this->service()->forClient($client)->credits, 'Service debt is debt: it must block the credit.');
    }

    // ------------------------------------------------------------------ rule 4

    public function test_the_current_month_is_not_counted_before_its_boundary_closes(): void
    {
        $client = $this->client();
        $thisMonth = Carbon::now()->startOfMonth()->toDateString();

        $this->pay($this->invoice($client, $thisMonth), $client, Carbon::now()->toDateString());

        $progress = $this->service()->forClient($client);

        $this->assertSame(0, $progress->credits, 'An open boundary must never be credited.');
        $this->assertSame(0.0, $progress->balance, 'The month is paid, but still open.');
    }

    // ------------------------------------------------------------------ rule 5

    public function test_twin_invoices_of_one_month_are_one_obligation(): void
    {
        $client = $this->client();
        $dates  = $this->monthlyDues(3);

        $first = $this->invoice($client, $dates[0]);
        $twin  = $this->invoice($client, $dates[0]);        // same month, twin
        $this->pay($first, $client, $this->settleDate($dates[0]));
        $this->pay($twin, $client, $this->settleDate($dates[0]));

        $this->pay($this->invoice($client, $dates[1]), $client, $this->settleDate($dates[1]));

        // two bills, one obligation: the month must be counted ONCE, never twice.
        $progress = $this->service()->forClient($client);

        $this->assertSame(2, $progress->credits, 'Two months billed: the twin month counts once, the next month once.');
        $creditedMonths = array_values(array_map(
            fn ($m) => $m['month'],
            array_filter($progress->months, fn ($m) => $m['credited'])
        ));

        $this->assertSame(
            [Carbon::parse($dates[0])->format('Y-m'), Carbon::parse($dates[1])->format('Y-m')],
            $creditedMonths,
            'A month may appear at most once among the credited months.'
        );
    }

    // ------------------------------------------------------------------ threshold

    public function test_eligibility_follows_the_configured_threshold(): void
    {
        $client = $this->client();
        $dates  = $this->monthlyDues(4);

        foreach ($dates as $d) {
            $this->pay($this->invoice($client, $d), $client, $this->settleDate($d));
        }

        $this->assertSame(4, $this->service()->forClient($client)->credits);

        BonusSettings::set('bonus_months_required', 3);
        BonusSettings::flush();
        $this->assertTrue($this->service()->forClient($client)->eligible, '3 of 3 must be eligible.');

        BonusSettings::set('bonus_months_required', 24);
        BonusSettings::flush();
        $this->assertFalse($this->service()->forClient($client)->eligible, '3 of 24 must not be eligible.');
    }

    // ------------------------------------------------------------------ reset

    public function test_an_issued_free_month_restarts_the_counter(): void
    {
        $client = $this->client();
        $dates  = $this->monthlyDues(5);

        foreach ($dates as $d) {
            $this->pay($this->invoice($client, $d), $client, $this->settleDate($d));
        }

        $this->assertSame(5, $this->service()->forClient($client)->credits);

        // a free month was issued mid-cycle (11 Aug, after four closed boundaries)
        DB::table('tbl_client_bonuses')->insert([
            'client_id'   => $client,
            'earned_at'   => $dates[1],
            'status'      => ClientBonus::STATUS_REDEEMED,
            'source'      => 'manual',
            'redeemed_at' => Carbon::parse($dates[3])->addDays(10)->toDateTimeString(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->assertSame(2, $this->service()->forClient($client)->credits, 'Only months after the issued one count again.');
    }

    // ------------------------------------------------------------------ lists

    public function test_the_eligible_list_is_ranked_and_carries_the_cost(): void
    {
        BonusSettings::set('bonus_months_required', 2);
        BonusSettings::flush();

        $dates = $this->monthlyDues(4);

        $best = $this->client('الأفضل', 'internet', true, 30);
        $good = $this->client('جيد', 'internet', true, 15);
        $late = $this->client('متأخر', 'internet', true, 20);

        // best: all 3 closed months clean
        foreach ($dates as $d) {
            $this->pay($this->invoice($best, $d), $best, $this->settleDate($d));
        }
        // good: 2 clean months, then nothing
        $this->pay($this->invoice($good, $dates[0]), $good, $this->settleDate($dates[0]));
        $this->pay($this->invoice($good, $dates[1]), $good, $this->settleDate($dates[1]));
        // late: qualifies but only just
        $this->pay($this->invoice($late, $dates[0]), $late, $this->settleDate($dates[0]));
        $this->pay($this->invoice($late, $dates[1]), $late, $this->settleDate($dates[1]));
        $this->pay($this->invoice($late, $dates[2]), $late, $this->settleDate($dates[2]));

        $service = $this->service();
        $eligible = $service->eligibleClients();

        $this->assertSame([$best, $late, $good], array_column($eligible, 'clientId'), 'Ranked: most credits first, then the oldest last credit.');
        $this->assertSame(65.0, $service->costOf(array_column($eligible, 'clientId')), 'Cost is the sum of the customers\' prices.');
    }

    public function test_a_customer_with_debt_never_appears_in_the_eligible_list(): void
    {
        BonusSettings::set('bonus_months_required', 1);
        BonusSettings::flush();

        $dates = $this->monthlyDues(3);
        $clean = $this->client('نظيف');
        $dirty = $this->client('عليه دين');

        $this->pay($this->invoice($clean, $dates[0]), $clean, $this->settleDate($dates[0]));
        $this->pay($this->invoice($clean, $dates[1]), $clean, $this->settleDate($dates[1]));

        $this->pay($this->invoice($dirty, $dates[0]), $dirty, $this->settleDate($dates[0]));
        $this->pay($this->invoice($dirty, $dates[1]), $dirty, $this->settleDate($dates[1]));
        $this->invoice($dirty, $dates[1], ['invoice_type' => 'service', 'amount' => '40.00']);

        $ids = array_column($this->service()->eligibleClients(), 'clientId');

        $this->assertContains($clean, $ids);
        $this->assertNotContains($dirty, $ids, 'Debt disqualifies, even when the month count is enough.');
    }

    // ------------------------------------------------------------------ preview

    public function test_a_threshold_preview_states_the_count_and_the_cost_before_saving(): void
    {
        $dates = $this->monthlyDues(4);
        $a = $this->client('أ', 'internet', true, 15);
        $b = $this->client('ب', 'internet', true, 25);

        foreach ([$a, $b] as $id) {
            $this->pay($this->invoice($id, $dates[0]), $id, $this->settleDate($dates[0]));
            $this->pay($this->invoice($id, $dates[1]), $id, $this->settleDate($dates[1]));
        }

        $service = $this->service();

        $this->assertSame(['count' => 2, 'cost' => 40.0], $service->preview(2));
        $this->assertSame(['count' => 0, 'cost' => 0.0], $service->preview(12));
    }

    // ------------------------------------------------------------------ helpers

    private function service(): SimpleBonusProgressService
    {
        return app(SimpleBonusProgressService::class);
    }

    private function seedSettings(): void
    {
        foreach ([
            'bonus_enabled'          => '1',
            'bonus_months_required'  => '12',
            'bonus_grace_days'       => '5',
            'bonus_count_start_date' => $this->monthlyDues(8)[0],
        ] as $key => $value) {
            BonusSettings::set($key, $value);
        }

        BonusSettings::flush();
    }

    /** Four monthly due dates, oldest first, all comfortably in the past. */
    private function monthlyDues(int $n): array
    {
        $dues = [];

        for ($i = $n; $i >= 1; $i--) {
            $dues[] = Carbon::now()->startOfMonth()->subMonthsNoOverflow($i)->toDateString();
        }

        return $dues;
    }

    /** Pay inside the month itself, so the boundary is always clean. */
    private function settleDate(string $dueDate): string
    {
        return Carbon::parse($dueDate)->addDays(1)->toDateString();
    }

    private function client(string $name = 'عميل بونص', string $type = 'internet', bool $active = true, float $price = 15.0): int
    {
        return DB::table('tbl_clients')->insertGetId([
            'name'              => $name,
            'phone'             => '0555'.random_int(100000, 999999),
            'address1'          => 'عنوان',
            'client_type'       => $type,
            'subscription_id'   => 1,
            'price'             => $price,
            'subscription_date' => '2026-01-01',
            'start_date'        => '2026-01-01',
            'is_active'         => $active ? '1' : '0',
            'created_at'        => now(),
            'updated_at'        => now(),
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

    public function test_the_next_eligible_month_counts_the_open_boundaries_as_winnable(): void
    {
        // Ten monthly invoices. The last one is unpaid and falls due in three days, so the
        // boundaries of the last TWO months are still in the future — both are still winnable.
        $lastDue = Carbon::today()->addDays(3);
        $dues = [];
        for ($i = 9; $i >= 0; $i--) {
            $dues[] = $lastDue->copy()->subMonthsNoOverflow($i)->toDateString();
        }

        foreach ([
            'bonus_enabled'          => '1',
            'bonus_months_required'  => '10',
            'bonus_grace_days'       => '0',
            'bonus_count_start_date' => $dues[0],
        ] as $key => $value) {
            BonusSettings::set($key, $value);
        }
        BonusSettings::flush();

        $clientId = $this->client();
        foreach ($dues as $i => $due) {
            $invoiceId = $this->invoice($clientId, $due);
            if ($i === count($dues) - 1) {
                continue;   // the last invoice stays unpaid
            }
            $this->pay($invoiceId, $clientId, Carbon::parse($due)->subDays(5)->toDateString());
        }

        $progress = $this->service()->forClient($clientId, true);
        $this->assertSame(8, $progress->credits);
        $this->assertSame(10, $progress->required);
        $this->assertSame(2, $progress->remainingToEarn());

        // honoring both open months finishes the requirement in the LAST invoice's month —
        // "current month + months left" would have said two months later than the truth
        $this->assertSame(
            Carbon::parse($dues[9])->format('Y-m'),
            $progress->nextEligibleMonth,
        );
        $this->assertNotSame(
            Carbon::now()->startOfMonth()->addMonthsNoOverflow(2)->format('Y-m'),
            $progress->nextEligibleMonth,
        );
    }
}
