<?php

namespace App\Services\Bonus;

use App\Models\ClientBonus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Feature 021 — computes a customer's loyalty progress from the real invoices.
 *
 * Nothing is stored: the number always matches the payments, so a restore or a
 * payment edit can never leave a stale counter behind.
 *
 * Rules (locked with the owner, 2026-10-21):
 *  - a month is credited when it is fully paid before the next month's invoice
 *    is due ("paid inside its own month"), plus an admin-editable grace in days;
 *  - oldest-first: a month is never credited while an older invoice is still
 *    open — paying the newest bill and leaving the oldest unpaid earns nothing
 *    (the "دوامة" / spin customer);
 *  - one catch-up (استدراك) per cycle: a 1–2 month break cleared before the
 *    following bill credits the late month too;
 *  - a break longer than `bonus_hard_reset_after_months` zeroes the run;
 *  - carried arrears on `bonus_spiral_after_months` consecutive boundaries put
 *    the customer in the spin state (progress reported as 0) until he clears.
 */
class BonusProgressService
{
    public const STATE_ON_TIME = 'on_time';
    public const STATE_CATCHUP = 'catchup';
    public const STATE_LATE    = 'late';
    public const STATE_UNPAID  = 'unpaid';

    // Payment-habit bands, in days of delay after the invoice's own due date.
    // Chosen from the live distribution (2026-10-22): per-customer medians are
    // p25 = 2, p50 = 5, p75 = 14, p90 = 29 — five bands is the most the data supports.
    public const PATTERN_WEEK       = 7;
    public const PATTERN_LATE_HABIT = 15;
    public const PATTERN_SLOW       = 30;

    public function forClient(int $clientId, bool $withMonths = true): BonusProgress
    {
        $map = $this->forClients([$clientId], $withMonths);

        return $map[$clientId] ?? $this->emptyProgress($clientId);
    }

    /**
     * One pass for a whole page: three queries total, never one per client.
     *
     * @param  array<int,int>  $clientIds
     * @return array<int,BonusProgress>
     */
    public function forClients(array $clientIds, bool $withMonths = true): array
    {
        $clientIds = array_values(array_unique(array_map('intval', $clientIds)));

        $result = [];
        foreach ($clientIds as $id) {
            $result[$id] = $this->emptyProgress($id);
        }

        if ($clientIds === []) {
            return $result;
        }

        // Out of the program (owner decisions, 2026-10-21):
        //  - satellite customers,
        //  - and any customer who is not active.
        // They are never counted even if a caller forgets to filter them out.
        $allowedTypes = BonusSettings::clientTypes();

        $rows = DB::table('tbl_clients')
            ->whereIn('id', $clientIds)
            ->get(['id', 'client_type', 'is_active']);

        $state = [];
        foreach ($rows as $row) {
            if ((int) $row->is_active !== 1) {
                $state[(int) $row->id] = 'inactive';
            } elseif (! in_array($row->client_type, $allowedTypes, true)) {
                $state[(int) $row->id] = 'excluded';
            }
        }

        $eligibleIds = [];
        foreach ($clientIds as $id) {
            if (isset($state[$id])) {
                $blocked = $this->emptyProgress($id);
                $blocked->status = $state[$id];
                $result[$id] = $blocked;
            } else {
                $eligibleIds[] = $id;
            }
        }

        $clientIds = $eligibleIds;

        if ($clientIds === []) {
            return $result;
        }

        $required    = BonusSettings::monthsRequired();
        $graceDays   = BonusSettings::graceDays();
        $catchupMax  = BonusSettings::catchupPerCycle();
        $hardReset   = BonusSettings::hardResetAfterMonths();
        $spiralAfter = BonusSettings::spiralAfterMonths();
        $globalStart = BonusSettings::countStartDate();

        $bonuses = DB::table('tbl_client_bonuses')
            ->whereIn('client_id', $clientIds)
            ->orderBy('id')
            ->get(['client_id', 'status', 'earned_at', 'redeemed_at', 'redeemed_invoice_id']);

        $adjustments = DB::table('tbl_client_bonus_adjustments')
            ->whereIn('client_id', $clientIds)
            ->orderBy('id')
            ->get(['client_id', 'type', 'points', 'created_at']);

        $bonusInvoiceIds = [];
        $sinceByClient  = [];
        $openBonus      = [];
        $manualPoints   = [];

        foreach ($bonuses as $b) {
            if ($b->redeemed_invoice_id) {
                $bonusInvoiceIds[] = (int) $b->redeemed_invoice_id;
            }

            $since = $globalStart;
            if ($b->redeemed_at) {
                $redeemedDate = Carbon::parse($b->redeemed_at)->toDateString();
                if ($redeemedDate > $since) {
                    $since = $redeemedDate;
                }
            }
            $sinceByClient[(int) $b->client_id] = $since;

            if (in_array($b->status, [ClientBonus::STATUS_PENDING, ClientBonus::STATUS_APPROVED], true)) {
                $openBonus[(int) $b->client_id] = true;
            }
        }

        // Rows are read in insertion order: a reset moves the cycle origin forward
        // AND wipes every point granted before it.
        foreach ($adjustments as $adj) {
            $clientId = (int) $adj->client_id;

            if ($adj->type === 'reset') {
                $resetDate = Carbon::parse($adj->created_at)->toDateString();
                if ($resetDate > ($sinceByClient[$clientId] ?? $globalStart)) {
                    $sinceByClient[$clientId] = $resetDate;
                }
                $manualPoints[$clientId] = 0;
                continue;
            }

            $since = $sinceByClient[$clientId] ?? $globalStart;
            if (Carbon::parse($adj->created_at)->toDateString() >= $since) {
                $manualPoints[$clientId] = ($manualPoints[$clientId] ?? 0) + (int) $adj->points;
            }
        }

        $invoices = DB::table('tbl_invoices')
            ->whereIn('client_id', $clientIds)
            ->whereNull('deleted_at')
            ->where('invoice_type', 'subscription')
            ->whereNotNull('due_date')
            ->where('due_date', '>=', $globalStart)
            ->when($bonusInvoiceIds !== [], fn ($q) => $q->whereNotIn('id', $bonusInvoiceIds))
            ->orderBy('due_date')
            ->get(['id', 'client_id', 'due_date', 'amount', 'remaining_amount', 'status', 'paid_date']);

        $invoiceIds = $invoices->pluck('id')->all();

        $paidAt = [];
        if ($invoiceIds !== []) {
            $rows = DB::table('tbl_revenues')
                ->whereIn('invoice_id', $invoiceIds)
                ->whereNull('deleted_at')
                ->groupBy('invoice_id')
                ->selectRaw('invoice_id, MIN(created_at) AS first_paid_at')
                ->get();

            foreach ($rows as $r) {
                $paidAt[(int) $r->invoice_id] = $r->first_paid_at
                    ? Carbon::parse($r->first_paid_at)
                    : null;
            }
        }

        $grouped = [];
        foreach ($invoices as $inv) {
            $grouped[(int) $inv->client_id][] = $inv;
        }

        foreach ($clientIds as $clientId) {
            $since = $sinceByClient[$clientId] ?? $globalStart;

            $clientInvoices = array_values(array_filter(
                $grouped[$clientId] ?? [],
                fn ($inv) => $inv->due_date >= $since
            ));

            $progress = $this->compute(
                $clientId,
                $clientInvoices,
                $paidAt,
                $required,
                $graceDays,
                $catchupMax,
                $hardReset,
                $spiralAfter,
                $withMonths,
                $manualPoints[$clientId] ?? 0
            );

            $progress->hasOpenBonus = $openBonus[$clientId] ?? false;
            $progress->eligible     = $progress->progress >= $required && ! $progress->hasOpenBonus;

            $result[$clientId] = $progress;
        }

        return $result;
    }

    /**
     * @param  array<int,object>  $invoices  sorted by due_date asc
     * @param  array<int,?Carbon> $paidAt    invoice id => first payment moment
     */
    protected function compute(
        int $clientId,
        array $invoices,
        array $paidAt,
        int $required,
        int $graceDays,
        int $catchupMax,
        int $hardReset,
        int $spiralAfter,
        bool $withMonths = true,
        int $manualPoints = 0
    ): BonusProgress {
        $progress = $this->emptyProgress($clientId);
        $progress->required = $required;

        // A bill that is not due yet can never break the run: invoices are often
        // generated a month ahead (241 of them on the live data).
        $today = Carbon::today();
        $invoices = array_values(array_filter(
            $invoices,
            fn ($inv) => Carbon::parse($inv->due_date)->lte($today)
        ));

        $count = count($invoices);
        if ($count === 0) {
            // No invoice in the current cycle yet — admin-granted points still count.
            $progress->progress = max(0, min($required, $manualPoints));

            return $progress;
        }

        $progress->cycleStart = $invoices[0]->due_date;

        // Pre-compute the due dates so the "next bill" window is exact.
        $dues = [];
        foreach ($invoices as $inv) {
            $dues[] = Carbon::parse($inv->due_date)->startOfDay();
        }

        $credited    = [];
        $creditedMonths = [];   // Y-m already credited — one credit per month, max
        $settledAt   = [];
        $windowEnds  = [];
        $catchupsUsed = 0;
        $lastCatchupMonth = null;
        $states = [];               // state per invoice index
        $lags   = [];               // delay in days for every settled invoice
        $everLateAfterDue = false;
        $hardResetAt = -1;          // index of the boundary that wiped the run
        $carriedRun  = 0;
        $maxCarried  = 0;
        $lastBreak   = 0;

        // The window of a month ends at the NEXT MONTH's bill, never at another invoice
        // that belongs to the same month (duplicate / split bills share the due date).
        $nextDistinct = [];
        for ($i = 0; $i < $count; $i++) {
            $monthKey = $dues[$i]->format('Y-m');
            $nextDistinct[$i] = null;
            for ($j = $i + 1; $j < $count; $j++) {
                if ($dues[$j]->format('Y-m') !== $monthKey) {
                    $nextDistinct[$i] = $j;
                    break;
                }
            }
        }

        for ($i = 0; $i < $count; $i++) {
            $due = $dues[$i];

            $payTime = null;
            if ((float) $invoices[$i]->remaining_amount <= 0) {
                $payTime = $paidAt[(int) $invoices[$i]->id] ?? null;
                if ($payTime === null && $invoices[$i]->paid_date) {
                    $payTime = Carbon::parse($invoices[$i]->paid_date);
                }
            }
            $settledAt[$i] = $payTime;

            // window end = the NEXT MONTH's due date (+ grace); last month rolls one month.
            $nd = $nextDistinct[$i];
            $windowEnd = $nd !== null
                ? $dues[$nd]->copy()
                : $due->copy()->addMonthNoOverflow();
            $windowEnd->addDays($graceDays)->endOfDay();

            $nd2 = $nd !== null ? $nextDistinct[$nd] : null;
            $catchupEnd = $nd2 !== null
                ? $dues[$nd2]->copy()
                : $windowEnd->copy();
            $catchupEnd->addDays($graceDays)->endOfDay();

            $windowEnds[$i] = $windowEnd->copy();

            // oldest-first: was any older invoice still open at this month's due date?
            $olderOpen = false;
            for ($j = 0; $j < $i; $j++) {
                if ($settledAt[$j] === null) {
                    $olderOpen = true;
                    break;
                }
                if ($settledAt[$j]->gt($due) && empty($credited[$j])) {
                    $olderOpen = true;
                    break;
                }
            }

            // carried arrears streak (spin detection)
            if ($olderOpen) {
                $carriedRun++;
                $maxCarried = max($maxCarried, $carriedRun);
            } else {
                $carriedRun = 0;
            }

            // break length: consecutive preceding months still open at this due date
            $break = 0;
            for ($j = $i - 1; $j >= 0; $j--) {
                if ($settledAt[$j] === null || $settledAt[$j]->gt($due)) {
                    $break++;
                } else {
                    break;
                }
            }
            $lastBreak = $break;

            if ($hardReset > 0 && $break > $hardReset) {
                $hardResetAt = $i;
            }

            $isCredited = false;
            $state = self::STATE_UNPAID;

            if ($payTime !== null) {
                if ($payTime->lte($windowEnd) && ! $olderOpen) {
                    $isCredited = true;
                    $state = self::STATE_ON_TIME;
                } elseif ($payTime->lte($windowEnd) && $olderOpen) {
                    // paid inside its month but an older bill was still open → no credit
                    $state = self::STATE_LATE;
                } elseif ($payTime->lte($catchupEnd) && $catchupsUsed < $catchupMax) {
                    // catch-up: the 1–2 month break was cleared before the following bill
                    $isCredited = true;
                    $catchupsUsed++;
                    $lastCatchupMonth = $due->format('Y-m');
                    $state = self::STATE_CATCHUP;
                } else {
                    $state = self::STATE_LATE;
                }
            }

            $monthKey = $due->format('Y-m');

            if ($isCredited && isset($creditedMonths[$monthKey])) {
                // Two invoices in the same calendar month (the billing day changed):
                // the second one never earns a second point.
                $isCredited = false;
                $state = 'duplicate';
            }

            if ($isCredited) {
                $creditedMonths[$monthKey] = true;
            }

            $credited[$i] = $isCredited;
            $states[$i] = $state;

            if ($payTime !== null) {
                $lags[] = (int) floor(
                    ($payTime->copy()->startOfDay()->timestamp - $due->copy()->startOfDay()->timestamp) / 86400
                );
            }

            // Punctual means paid on or before the invoice's OWN due date.
            if ($payTime !== null && $payTime->gt($due->copy()->addDays($graceDays)->endOfDay())) {
                $everLateAfterDue = true;
            }

            if ($isCredited) {
                $progress->lastCreditedMonth = $due->format('Y-m');
            }

            if ($withMonths) {
                $progress->months[] = [
                    'month'    => $due->format('Y-m'),
                    'due'      => $invoices[$i]->due_date,
                    'state'    => $state,
                    'credited' => $isCredited,
                    'paid_at'  => $payTime?->toDateTimeString(),
                ];
            }
        }

        $progress->catchupsUsed   = $catchupsUsed;
        $progress->lastCatchupMonth = $lastCatchupMonth;

        // Progress = credits earned since the last hard reset.
        $credits = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($i <= $hardResetAt) {
                continue;
            }
            if (! empty($credited[$i])) {
                $credits++;
            }
        }

        // ---------------------------------------------------------------- habit
        // The median delay describes HOW this customer pays; it is deliberately
        // independent from where he stands today, so a late payer who is current
        // is never confused with the person who pays on the due date.
        $medianLag = null;
        if ($lags !== []) {
            sort($lags);
            $mid = intdiv(count($lags), 2);
            $medianLag = count($lags) % 2 === 1
                ? $lags[$mid]
                : (int) round(($lags[$mid - 1] + $lags[$mid]) / 2);
        }

        $pattern = null;
        if ($medianLag !== null) {
            $pattern = $medianLag <= 0 ? 'precise'
                : ($medianLag <= self::PATTERN_WEEK ? 'week'
                : ($medianLag <= self::PATTERN_LATE_HABIT ? 'late_habit'
                : ($medianLag <= self::PATTERN_SLOW ? 'slow' : 'very_slow')));
        }

        $progress->medianLag = $medianLag;
        $progress->pattern   = $pattern;

        // Admin-granted points are added to whatever the invoices produced.
        $credits = max(0, $credits + $manualPoints);

        // Status describes the customer's position TODAY, never his history, so a
        // customer who clears his arrears immediately stops being flagged.
        //   0 open months  -> منتظم (or استدراك if one was used this cycle)
        //   1 open month   -> متأخر
        //   2 open months  -> دوامة  (pays one, stays one behind)
        //   3+ open months -> متعثر  (broke more than two months)
        // A month is only "overdue" once its window has closed: being unpaid in the
        // current month is normal, not a violation.
        $openCount = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($settledAt[$i] === null && $windowEnds[$i]->lt(now())) {
                $openCount++;
            }
        }

        // Clean months: how many months, counting backwards from the newest, were paid
        // inside their own month. A month that is not due yet neither helps nor hurts.
        $cleanMonths = 0;
        $hadBreak    = false;
        for ($i = $count - 1; $i >= 0; $i--) {
            $state = $states[$i] ?? self::STATE_UNPAID;

            if ($state === self::STATE_ON_TIME) {
                $cleanMonths++;
                continue;
            }
            if ($state === self::STATE_UNPAID && ! $windowEnds[$i]->lt(now())) {
                continue;   // the current month is not late yet
            }
            $hadBreak = true;
            break;
        }
        if (in_array(self::STATE_CATCHUP, $states, true) || in_array(self::STATE_LATE, $states, true)) {
            $hadBreak = true;
        }

        $progress->cleanMonths       = $cleanMonths;
        $progress->hadBreak          = $hadBreak;
        $progress->everLateAfterDue  = $everLateAfterDue;
        $recoveryMonths              = BonusSettings::recoveryMonths();

        // Is he still paying *newer* bills while an old one stays open? That is the
        // «دوامة» pattern, versus a customer who simply stopped paying.
        $oldestOpenDue = null;
        $lastPayment   = null;
        for ($i = 0; $i < $count; $i++) {
            if ($settledAt[$i] === null) {
                if ($oldestOpenDue === null && $windowEnds[$i]->lt(now())) {
                    $oldestOpenDue = $dues[$i];
                }
            } elseif ($lastPayment === null || $settledAt[$i]->gt($lastPayment)) {
                $lastPayment = $settledAt[$i];
            }
        }

        $isSpin = $oldestOpenDue !== null
            && $lastPayment !== null
            && $lastPayment->gt($oldestOpenDue)
            && $openCount >= 2;

        $progress->carriedArrears = $openCount;

        // What is left before the next classification, and the date it happens.
        $progress->monthsToSpiral    = max(0, $spiralAfter - $openCount);
        $progress->monthsToHardReset = max(0, ($hardReset + 1) - $openCount);

        $boundary = $windowEnds[$count - 1] ?? null;
        if ($boundary !== null) {
            $progress->nextBoundaryDate   = $boundary->toDateString();
            $progress->daysToNextBoundary = max(0, (int) Carbon::today()->diffInDays($boundary->copy()->startOfDay(), false));
        }

        if ($openCount > $hardReset) {
            $progress->progress = 0;
            $progress->status   = 'hard_reset';
        } elseif ($openCount >= 1) {
            $progress->progress = min($credits, $required);
            $progress->status   = $isSpin ? 'spiral' : 'late';
        } else {
            $progress->progress = min($credits, $required);
            // The status must describe TODAY, and it must be earned: after any break
            // the customer is «عائد للانتظام» until he strings together N clean months.
            // A customer who never broke a month in the cycle gets the highest tier.
            $progress->status = $hadBreak
                ? ($cleanMonths >= $recoveryMonths ? 'compliant' : 'recovering')
                : ($cleanMonths >= $recoveryMonths && ! $everLateAfterDue ? 'excellent' : 'compliant');

        }

        // Commitment score 0..100 — a smooth rank instead of another badge.
        //   45% where he stands today, 35% how many months he actually settled,
        //   20% how punctual he is. A two-day delay nudges the score, it never
        //   drops a customer a whole tier.
        $monthsConsidered = count(array_unique(array_map(fn ($d) => $d->format('Y-m'), $dues)));
        $cleanShare  = $monthsConsidered > 0 ? min(1.0, $credits / $monthsConsidered) : 0.0;
        if ($medianLag === null) {
            // Nothing has ever been paid: there is no behaviour to score yet.
            $progress->score = 0;
        } else {
            $punctuality = max(0.0, 1 - max(0, $medianLag) / 30);
            $progress->score = (int) round(
                45 * (1 - min(1, $openCount / 3))
                + 35 * $cleanShare
                + 20 * $punctuality
            );
        }

        $remaining = max(0, $required - $progress->progress);
        $progress->nextEligibleMonth = Carbon::now()
            ->startOfMonth()
            ->addMonthsNoOverflow($remaining)
            ->format('Y-m');

        return $progress;
    }

    protected function emptyProgress(int $clientId): BonusProgress
    {
        $progress = new BonusProgress();
        $progress->clientId = $clientId;
        $progress->required = BonusSettings::monthsRequired();

        return $progress;
    }

    /**
     * Admin list helper: customers whose newest bills are paid while older ones
     * stay open (the "دوامة" tab).
     *
     * @return array<int,array{client_id:int,name:string,unpaid_count:int,oldest_due:string,days_old:int,owed:float,last_payment:?string}>
     */
    public function spiralClients(int $limit = 100): array
    {
        // Aggregate the payments once, then join — the previous correlated
        // sub-select cost ~5.5s for 936 clients.
        $lastPayments = DB::table('tbl_revenues')
            ->whereNull('deleted_at')
            ->groupBy('client_id')
            ->selectRaw('client_id, MAX(created_at) as last_payment');

        $rows = DB::table('tbl_clients as c')
            ->join('tbl_invoices as i', function ($join) {
                $join->on('i.client_id', '=', 'c.id')
                    ->whereNull('i.deleted_at')
                    ->where('i.invoice_type', '=', 'subscription')
                    ->where('i.remaining_amount', '>', 0);
            })
            ->leftJoinSub($lastPayments, 'rp', 'rp.client_id', '=', 'c.id')
            ->whereNull('c.deleted_at')
            ->where('c.is_active', 1)
            ->whereIn('c.client_type', BonusSettings::clientTypes())
            ->groupBy('c.id', 'c.name', 'rp.last_payment')
            // دوامة = he paid something *after* the oldest open bill existed, i.e. he
            // keeps paying newer bills and leaves the old one behind. Time-window free,
            // so it stays correct on any data snapshot.
            ->havingRaw('COUNT(i.id) >= 2')
            ->havingRaw('DATEDIFF(CURDATE(), MIN(i.due_date)) >= 35')
            ->havingRaw('rp.last_payment > MIN(i.due_date)')
            ->selectRaw('c.id as client_id, c.name, COUNT(i.id) as unpaid_count,
                         MIN(i.due_date) as oldest_due,
                         SUM(i.remaining_amount) as owed,
                         rp.last_payment as last_payment')
            ->orderByRaw('MIN(i.due_date) asc')
            ->limit($limit)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'client_id'    => (int) $r->client_id,
                'name'         => $r->name,
                'unpaid_count' => (int) $r->unpaid_count,
                'oldest_due'   => $r->oldest_due,
                'days_old'     => $r->oldest_due ? (int) Carbon::parse($r->oldest_due)->diffInDays(now()) : 0,
                'owed'         => (float) $r->owed,
                'last_payment' => $r->last_payment,
            ];
        }

        return $out;
    }
}
