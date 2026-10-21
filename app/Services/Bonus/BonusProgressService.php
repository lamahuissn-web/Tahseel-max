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

        // Satellite customers are out of the program (owner decision 2026-10-21),
        // so they are never counted even if a caller forgets to filter them out.
        $allowedTypes = BonusSettings::clientTypes();

        $eligibleIds = DB::table('tbl_clients')
            ->whereIn('id', $clientIds)
            ->whereIn('client_type', $allowedTypes)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $eligibleLookup = array_flip($eligibleIds);

        foreach ($clientIds as $id) {
            if (! isset($eligibleLookup[$id])) {
                $excluded = $this->emptyProgress($id);
                $excluded->status = 'excluded';
                $result[$id] = $excluded;
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

        $bonusInvoiceIds = [];
        $sinceByClient  = [];
        $openBonus      = [];

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
                $withMonths
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
        bool $withMonths = true
    ): BonusProgress {
        $progress = $this->emptyProgress($clientId);
        $progress->required = $required;

        $count = count($invoices);
        if ($count === 0) {
            return $progress;
        }

        $progress->cycleStart = $invoices[0]->due_date;

        // Pre-compute the due dates so the "next bill" window is exact.
        $dues = [];
        foreach ($invoices as $inv) {
            $dues[] = Carbon::parse($inv->due_date)->startOfDay();
        }

        $credited    = [];
        $settledAt   = [];
        $catchupsUsed = 0;
        $hardResetAt = -1;          // index of the boundary that wiped the run
        $carriedRun  = 0;
        $maxCarried  = 0;
        $lastBreak   = 0;

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

            // window end = the next bill's due date (+ grace); last month rolls one month.
            $windowEnd = $i + 1 < $count
                ? $dues[$i + 1]->copy()
                : $due->copy()->addMonthNoOverflow();
            $windowEnd->addDays($graceDays)->endOfDay();

            $catchupEnd = $i + 2 < $count
                ? $dues[$i + 2]->copy()
                : $windowEnd->copy();
            $catchupEnd->addDays($graceDays)->endOfDay();

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
                    $state = self::STATE_CATCHUP;
                } else {
                    $state = self::STATE_LATE;
                }
            }

            $credited[$i] = $isCredited;

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
        $progress->carriedArrears = $maxCarried;

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

        $isHardReset = $hardReset > 0 && $lastBreak > $hardReset;
        $isSpiral    = ! $isHardReset && $maxCarried >= $spiralAfter;

        if ($isHardReset) {
            $progress->progress = min($credits, $required);
            $progress->status   = 'hard_reset';
        } elseif ($isSpiral) {
            $progress->progress = 0;
            $progress->status   = 'spiral';
        } else {
            $progress->progress = min($credits, $required);
            if ($catchupMax > 0 && $catchupsUsed > 0) {
                $progress->status = 'catchup_used';
            } elseif ($lastBreak > 0) {
                $progress->status = 'late';
            } else {
                $progress->status = 'compliant';
            }
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
