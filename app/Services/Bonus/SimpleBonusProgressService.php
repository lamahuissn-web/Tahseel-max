<?php

namespace App\Services\Bonus;

use App\Models\ClientBonus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Feature 022 — the simplified bonus engine. One rule, nothing else.
 *
 * A month `Y-m` is credited iff the customer's TOTAL outstanding balance is zero
 * at that month's boundary:
 *
 *     boundary = due date of the NEXT month's subscription invoice
 *                (or this invoice's due date + 1 month when it is the last one)
 *                + bonus_grace_days, end of day
 *
 * "Total" means every non-deleted invoice, services included: a customer who is
 * clean on subscriptions but owes 40$ of services must not earn a free month.
 *
 * Counting is CUMULATIVE: a month with debt is simply not counted, and nothing is
 * taken back. Old arrears therefore block new credits until they are settled —
 * that is the collection incentive — and the day they are settled every later
 * month becomes creditable again.
 *
 * Nothing is written here: the progress is computed on read. Issuance stays a
 * human decision in BonusLedger.
 */
class SimpleBonusProgressService
{
    /** Amounts below this are rounding noise, not debt. */
    private const EPSILON = 0.01;

    private int $required;
    private int $graceDays;
    private Carbon $startDate;

    public function __construct()
    {
        $this->required  = BonusSettings::monthsRequired();
        $this->graceDays = BonusSettings::graceDays();
        $this->startDate = Carbon::parse(BonusSettings::countStartDate())->startOfDay();
    }

    /**
     * @param  array<int,int>  $clientIds
     * @return array<int,SimpleBonusProgress> keyed by client id
     */
    public function forClients(array $clientIds, bool $withMonths = false): array
    {
        $clientIds = array_values(array_unique(array_map('intval', $clientIds)));

        $result = [];
        foreach ($clientIds as $id) {
            $result[$id] = $this->blank($id);
        }

        if ($clientIds === []) {
            return $result;
        }

        $invoicesByClient = $this->loadInvoices($clientIds);
        $flows            = $this->loadFlows($invoicesByClient);
        $redeemed         = $this->loadRedeemed($clientIds);
        $open             = $this->loadOpenBonuses($clientIds);

        foreach ($clientIds as $id) {
            $result[$id] = $this->compute(
                $id,
                $invoicesByClient[$id] ?? [],
                $flows,
                $redeemed[$id] ?? null,
                isset($open[$id]),
                $withMonths
            );
        }

        return $result;
    }

    public function forClient(int $clientId, bool $withMonths = true): SimpleBonusProgress
    {
        return $this->forClients([$clientId], $withMonths)[$clientId];
    }

    /**
     * The ranking the owner decides from: most credits first, then the customer
     * who reached his credits earliest. Debt disqualifies — it is not sorted away.
     *
     * @return array<int,array<string,mixed>>
     */
    public function eligibleClients(int $limit = 300): array
    {
        $rows = [];

        foreach ($this->forClients($this->inScopeClientIds()) as $progress) {
            if (! $progress->eligible) {
                continue;
            }

            $rows[] = [
                'clientId'          => $progress->clientId,
                'credits'           => $progress->credits,
                'required'          => $progress->required,
                'balance'           => round($progress->balance, 2),
                'lastCreditedMonth' => $progress->lastCreditedMonth,
            ];
        }

        usort($rows, function (array $a, array $b) {
            return [$b['credits'], $a['lastCreditedMonth'] ?? ''] <=> [$a['credits'], $b['lastCreditedMonth'] ?? ''];
        });

        return array_slice($rows, 0, $limit);
    }

    /**
     * Who is about to become eligible — for the budget, not for issuing.
     *
     * @return array<int,array<string,mixed>>
     */
    public function vergeClients(int $within = 2, int $limit = 300): array
    {
        $rows = [];

        foreach ($this->forClients($this->inScopeClientIds()) as $progress) {
            $gap = $progress->required - $progress->credits;

            if ($progress->eligible || $gap < 1 || $gap > $within) {
                continue;
            }

            $rows[] = [
                'clientId'          => $progress->clientId,
                'credits'           => $progress->credits,
                'remaining'         => $gap,
                'balance'           => round($progress->balance, 2),
                'nextEligibleMonth' => $progress->nextEligibleMonth,
            ];
        }

        usort($rows, fn (array $a, array $b) => [$a['remaining'], $b['credits']] <=> [$b['remaining'], $a['credits']]);

        return array_slice($rows, 0, $limit);
    }

    /** @param array<int,int> $clientIds */
    public function costOf(array $clientIds): float
    {
        if ($clientIds === []) {
            return 0.0;
        }

        return round((float) DB::table('tbl_clients')->whereIn('id', $clientIds)->sum('price'), 2);
    }

    /**
     * What a threshold would cost, BEFORE it is saved — the guard against opening
     * a financial commitment with one click.
     *
     * @return array{count:int,cost:float}
     */
    public function preview(int $monthsRequired): array
    {
        $ids = [];

        foreach ($this->forClients($this->inScopeClientIds(), false) as $progress) {
            if ($progress->credits >= $monthsRequired && $progress->balance <= self::EPSILON && ! $progress->hasOpenBonus) {
                $ids[] = $progress->clientId;
            }
        }

        return ['count' => count($ids), 'cost' => $this->costOf($ids)];
    }

    // ------------------------------------------------------------------ internals

    private function blank(int $clientId): SimpleBonusProgress
    {
        $progress = new SimpleBonusProgress();
        $progress->clientId = $clientId;
        $progress->required = $this->required;

        return $progress;
    }

    /**
     * @param  array<int,object>  $invoices
     * @param  array<int,array<int,array{0:int,1:float}>>  $flows
     */
    private function compute(
        int $clientId,
        array $invoices,
        array $flows,
        ?Carbon $redeemedAt,
        bool $hasOpenBonus,
        bool $withMonths
    ): SimpleBonusProgress {
        $progress = $this->blank($clientId);
        $progress->hasOpenBonus = $hasOpenBonus;

        if ($invoices === []) {
            $progress->nextEligibleMonth = $this->nextMonthFor($progress->credits);

            return $progress;
        }

        $today = Carbon::now();

        // Every non-deleted invoice counts for the balance.
        foreach ($invoices as $invoice) {
            $progress->balance += (float) $invoice->remaining_amount;
        }

        // Boundaries come from subscription months only, one per month.
        $months = [];
        foreach ($invoices as $invoice) {
            if ($invoice->invoice_type !== 'subscription') {
                continue;
            }

            $due = $this->dateOf($invoice);
            if ($due === null || $due->lt($this->startDate)) {
                continue;
            }

            $key = $due->format('Y-m');
            if (isset($months[$key])) {
                continue;   // the same obligation billed twice
            }

            $months[$key] = $invoice;
        }

        $keys = array_keys($months);
        sort($keys);

        foreach ($keys as $i => $key) {
            $invoice = $months[$key];
            $due     = $this->dateOf($invoice);

            $nextDue = isset($keys[$i + 1]) ? $this->dateOf($months[$keys[$i + 1]]) : null;
            $boundary = ($nextDue ?? $due->copy()->addMonthNoOverflow())
                ->copy()
                ->addDays($this->graceDays)
                ->endOfDay();

            if ($boundary->gt($today)) {
                continue;   // the month is not over yet — never credited early
            }

            if ($redeemedAt !== null && $boundary->lte($redeemedAt)) {
                continue;   // a free month already consumed this stretch
            }

            $balanceAtBoundary = $this->balanceAt($invoices, $flows, $boundary);
            $credited = $balanceAtBoundary <= self::EPSILON;

            if ($credited) {
                $progress->credits++;
                $progress->lastCreditedMonth = $key;
            }

            if ($withMonths) {
                $progress->months[] = [
                    'month'    => $key,
                    'due'      => $due->toDateString(),
                    'credited' => $credited,
                    'balance'  => round($balanceAtBoundary, 2),
                    'paid_at'  => $this->firstPaymentAt($flows[$invoice->id] ?? []),
                ];
            }
        }

        $progress->status = $progress->balance <= self::EPSILON ? 'clean' : 'late';
        $progress->eligible = $progress->credits >= $this->required
            && $progress->balance <= self::EPSILON
            && ! $progress->hasOpenBonus;
        $progress->nextEligibleMonth = $this->nextMonthFor($progress->credits);

        return $progress;
    }

    /**
     * What the customer still owed at a given moment: for every invoice already
     * due, its amount minus everything received up to that moment.
     *
     * @param  array<int,object>  $invoices
     * @param  array<int,array<int,array{0:int,1:float}>>  $flows
     */
    private function balanceAt(array $invoices, array $flows, Carbon $boundary): float
    {
        $limit = $boundary->timestamp;
        $total = 0.0;

        foreach ($invoices as $invoice) {
            $due = $this->dateOf($invoice);
            if ($due === null || $due->gt($boundary)) {
                continue;
            }

            $paid = 0.0;
            foreach ($flows[$invoice->id] ?? [] as [$timestamp, $amount]) {
                if ($timestamp > $limit) {
                    break;
                }
                $paid += $amount;
            }

            $total += max(0.0, (float) $invoice->amount - $paid);
        }

        return $total;
    }

    private function nextMonthFor(int $credits): string
    {
        return Carbon::now()
            ->startOfMonth()
            ->addMonthsNoOverflow(max(0, $this->required - $credits))
            ->format('Y-m');
    }

    /** @param array<int,array{0:int,1:float}> $flow */
    private function firstPaymentAt(array $flow): ?string
    {
        if ($flow === []) {
            return null;
        }

        return Carbon::createFromTimestamp($flow[0][0])->toDateTimeString();
    }

    private function dateOf(object $invoice): ?Carbon
    {
        $raw = $invoice->due_date ?? $invoice->enshaa_date ?? null;

        return $raw === null ? null : Carbon::parse($raw)->startOfDay();
    }

    /** @param array<int,int> $clientIds @return array<int,array<int,object>> */
    private function loadInvoices(array $clientIds): array
    {
        $rows = DB::table('tbl_invoices')
            ->whereIn('client_id', $clientIds)
            ->whereNull('deleted_at')
            ->where('amount', '>', 0)
            ->orderBy('due_date')
            ->orderBy('id')
            ->get(['id', 'client_id', 'due_date', 'enshaa_date', 'amount', 'remaining_amount', 'invoice_type']);

        $byClient = [];
        foreach ($rows as $row) {
            $byClient[(int) $row->client_id][] = $row;
        }

        return $byClient;
    }

    /**
     * @param  array<int,array<int,object>>  $invoicesByClient
     * @return array<int,array<int,array{0:int,1:float}>>
     */
    private function loadFlows(array $invoicesByClient): array
    {
        $ids = [];
        foreach ($invoicesByClient as $invoices) {
            foreach ($invoices as $invoice) {
                $ids[] = (int) $invoice->id;
            }
        }

        if ($ids === []) {
            return [];
        }

        $flows = [];
        $rows = DB::table('tbl_revenues')
            ->whereIn('invoice_id', $ids)
            ->whereNull('deleted_at')
            ->orderBy('received_at')
            ->orderBy('id')
            ->get(['invoice_id', 'received_at', 'amount', 'created_at']);

        foreach ($rows as $row) {
            $at = $row->received_at ?? $row->created_at;
            if ($at === null) {
                continue;
            }

            $flows[(int) $row->invoice_id][] = [Carbon::parse($at)->timestamp, (float) $row->amount];
        }

        return $flows;
    }

    /** @param array<int,int> $clientIds @return array<int,Carbon> */
    private function loadRedeemed(array $clientIds): array
    {
        $rows = DB::table('tbl_client_bonuses')
            ->whereIn('client_id', $clientIds)
            ->whereNotNull('redeemed_at')
            ->orderBy('redeemed_at')
            ->get(['client_id', 'redeemed_at']);

        $latest = [];
        foreach ($rows as $row) {
            $latest[(int) $row->client_id] = Carbon::parse($row->redeemed_at);
        }

        return $latest;
    }

    /** @param array<int,int> $clientIds @return array<int,bool> */
    private function loadOpenBonuses(array $clientIds): array
    {
        $rows = DB::table('tbl_client_bonuses')
            ->whereIn('client_id', $clientIds)
            ->whereIn('status', [ClientBonus::STATUS_PENDING, ClientBonus::STATUS_APPROVED, ClientBonus::STATUS_POSTPONED])
            ->get(['client_id']);

        $open = [];
        foreach ($rows as $row) {
            $open[(int) $row->client_id] = true;
        }

        return $open;
    }

    /** @return array<int,int> */
    private function inScopeClientIds(): array
    {
        return DB::table('tbl_clients')
            ->whereNull('deleted_at')
            ->where('is_active', '1')
            ->where('client_type', 'internet')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
