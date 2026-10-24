<?php

namespace App\Services\Bonus;

use App\Models\ClientBonus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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

    /**
     * The overrides exist for ONE reason: the settings screen must preview a rule
     * before it is saved. Everything else reads the stored settings.
     */
    public function __construct(?int $required = null, ?int $graceDays = null, ?string $startDate = null)
    {
        $this->required  = $required ?? BonusSettings::monthsRequired();
        $this->graceDays = $graceDays ?? BonusSettings::graceDays();
        $this->startDate = Carbon::parse($startDate ?? BonusSettings::countStartDate())->startOfDay();
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
     * Both lists in ONE pass over the population. The list screen used to compute
     * the whole population twice (1.5s each on 950 customers), which is exactly the
     * kind of page an owner stops opening.
     *
     * Cached under a data fingerprint: any payment, invoice, bonus or settings
     * change produces a new key, so it can never serve a stale ranking.
     *
     * @return array{eligible:array<int,array<string,mixed>>,verge:array<int,array<string,mixed>>,eligibleCost:float,vergeCost:float,clean:int,late:int}
     */
    public function lists(int $vergeWithin = 2, int $limit = 300): array
    {
        $fingerprint = md5(implode('|', [
            (string) DB::table('tbl_invoices')->max('updated_at'),
            (string) DB::table('tbl_invoices')->count(),
            (string) DB::table('tbl_revenues')->count(),
            (string) DB::table('tbl_client_bonuses')->max('updated_at'),
            $this->required,
            $this->graceDays,
            $this->startDate->toDateString(),
        ]));

        return Cache::remember('bonus.lists.'.$fingerprint, now()->addMinutes(5), function () use ($vergeWithin, $limit) {
            $eligible = $verge = [];
            $eligibleCost = $vergeCost = 0.0;
            $clean = $late = 0;

            $ids = $this->inScopeClientIds();
            $prices = DB::table('tbl_clients')->whereIn('id', $ids)->pluck('price', 'id');

            foreach ($this->forClients($ids, false) as $progress) {
                $progress->status === 'clean' ? $clean++ : $late++;
                $price = (float) ($prices[$progress->clientId] ?? 0);

                if ($progress->eligible) {
                    $eligible[] = $this->listRow($progress, $price);
                    $eligibleCost += $price;

                    continue;
                }

                $gap = $progress->required - $progress->credits;
                if ($gap >= 1 && $gap <= $vergeWithin && $progress->balance <= self::EPSILON) {
                    $verge[] = $this->listRow($progress, $price);
                    $vergeCost += $price;
                }
            }

            usort($eligible, fn ($a, $b) => [$b['credits'], $a['lastCreditedMonth'] ?? '']
                <=> [$a['credits'], $b['lastCreditedMonth'] ?? '']);
            usort($verge, fn ($a, $b) => [$a['remaining'], $b['credits']] <=> [$b['remaining'], $a['credits']]);

            return [
                'eligible'     => array_slice($eligible, 0, $limit),
                'verge'        => array_slice($verge, 0, $limit),
                'eligibleCost' => round($eligibleCost, 2),
                'vergeCost'    => round($vergeCost, 2),
                'clean'        => $clean,
                'late'         => $late,
            ];
        });
    }

    /**
     * The follow-up dataset: EVERY customer in scope, with his progress, his state
     * and how he actually pays. This is what the owner reads every day — the eligible
     * list alone cannot answer "how is this customer doing?".
     *
     * Read-only. The money path (issuance) never touches this.
     *
     * @return array{rows:array<int,array<string,mixed>>,counts:array<string,int>,eligibleCost:float,vergeCost:float}
     */
    public function followUp(int $vergeWithin = 2): array
    {
        $fingerprint = md5(implode('|', [
            (string) DB::table('tbl_invoices')->max('updated_at'),
            (string) DB::table('tbl_invoices')->count(),
            (string) DB::table('tbl_revenues')->count(),
            (string) DB::table('tbl_client_bonuses')->max('updated_at'),
            $this->required,
            $this->graceDays,
            $this->startDate->toDateString(),
            'habit',
        ]));

        return Cache::remember('bonus.followup.'.$fingerprint, now()->addMinutes(5), function () use ($vergeWithin) {
            $ids = $this->inScopeClientIds();

            $clients = DB::table('tbl_clients')
                ->whereIn('id', $ids)
                ->get(['id', 'name', 'phone', 'price', 'client_type'])
                ->keyBy('id');

            $rows = [];
            $counts = ['all' => 0, 'eligible' => 0, 'verge' => 0, 'clean' => 0, 'late' => 0];
            $eligibleCost = $vergeCost = 0.0;

            foreach ($this->forClients($ids, true) as $progress) {
                $client = $clients[$progress->clientId] ?? null;
                if ($client === null) {
                    continue;
                }

                $price = (float) ($client->price ?? 0);
                $habit = $this->paymentHabit($progress);
                $gap   = $progress->required - $progress->credits;
                $verge = ! $progress->eligible
                    && $gap >= 1
                    && $gap <= $vergeWithin
                    && $progress->balance <= self::EPSILON;

                $rows[] = [
                    'clientId'          => $progress->clientId,
                    'name'              => $client->name,
                    'phone'             => (string) ($client->phone ?? ''),
                    'type'              => $client->client_type === 'internet' ? 'إنترنت' : 'ستلايت',
                    'price'             => $price,
                    'credits'           => $progress->credits,
                    'required'          => $progress->required,
                    'remaining'         => $progress->remainingToEarn(),
                    'balance'           => round($progress->balance, 2),
                    'overdue'           => round($progress->overdue, 2),
                    'status'            => $progress->status,
                    'statusLabel'       => $progress->statusLabel(),
                    'eligible'          => $progress->eligible,
                    'verge'             => $verge,
                    'hasOpenBonus'      => $progress->hasOpenBonus,
                    'lastCreditedMonth' => $progress->lastCreditedMonth,
                    'nextEligibleMonth' => $progress->nextEligibleMonth,
                    'streak'            => $habit['streak'],
                    'missed'            => $habit['missed'],
                    'daysLate'          => $habit['medianDays'],
                    'habitLabel'        => $habit['label'],
                    'months'            => array_slice($progress->months, -6),
                ];

                $counts['all']++;
                $progress->status === 'clean' ? $counts['clean']++ : $counts['late']++;

                if ($progress->eligible) {
                    $counts['eligible']++;
                    $eligibleCost += $price;
                } elseif ($verge) {
                    $counts['verge']++;
                    $vergeCost += $price;
                }
            }

            // who gets the free month first, then the closest to it
            usort($rows, function (array $a, array $b): int {
                $byEligible = (int) $b['eligible'] <=> (int) $a['eligible'];
                if ($byEligible !== 0) {
                    return $byEligible;
                }

                $byCredits = $b['credits'] <=> $a['credits'];
                if ($byCredits !== 0) {
                    return $byCredits;
                }

                $byMonth = (string) ($a['lastCreditedMonth'] ?? '~') <=> (string) ($b['lastCreditedMonth'] ?? '~');

                return $byMonth !== 0 ? $byMonth : strcmp((string) $a['name'], (string) $b['name']);
            });

            return [
                'rows'         => $rows,
                'counts'       => $counts,
                'eligibleCost' => round($eligibleCost, 2),
                'vergeCost'    => round($vergeCost, 2),
            ];
        });
    }

    /**
     * How this customer actually pays, described — never scored or banded.
     *
     * medianDays: positive = usually late by N days, negative = usually early.
     *
     * @return array{medianDays:?int,label:string,streak:int,missed:int}
     */
    private function paymentHabit(SimpleBonusProgress $progress): array
    {
        $deltas = [];
        $credited = [];
        $missed = 0;

        foreach ($progress->months as $month) {
            $credited[] = $month['credited'];

            if (! $month['credited']) {
                $missed++;
            }

            if ($month['paid_at'] === null) {
                continue;
            }

            $daysLate = Carbon::parse($month['due'])
                ->startOfDay()
                ->diffInDays(Carbon::parse($month['paid_at'])->startOfDay(), false);

            $deltas[] = (int) $daysLate;
        }

        $streak = 0;
        foreach (array_reverse($credited) as $ok) {
            if (! $ok) {
                break;
            }
            $streak++;
        }

        if ($deltas === []) {
            return [
                'medianDays' => null,
                'label'      => 'لا سجل دفع بعد',
                'streak'     => $streak,
                'missed'     => $missed,
            ];
        }

        sort($deltas);
        $count = count($deltas);
        $median = $count % 2 === 1
            ? $deltas[intdiv($count, 2)]
            : (int) round(($deltas[$count / 2 - 1] + $deltas[$count / 2]) / 2);

        $label = match (true) {
            $median <= -1 => 'يدفع قبل الاستحقاق بـ '.abs($median).' '.($this->daysWord(abs($median))),
            $median >= 1  => 'يتأخر عادةً '.$median.' '.$this->daysWord($median),
            default       => 'يدفع في يوم الاستحقاق تقريباً',
        };

        return [
            'medianDays' => $median,
            'label'      => $label,
            'streak'     => $streak,
            'missed'     => $missed,
        ];
    }

    private function daysWord(int $days): string
    {
        return match (true) {
            $days === 1  => 'يوم',
            $days === 2  => 'يومين',
            $days <= 10  => 'أيام',
            default      => 'يوماً',
        };
    }

    /** @return array<string,mixed> */
    private function listRow(SimpleBonusProgress $p, float $price): array
    {
        return [
            'clientId'          => $p->clientId,
            'credits'           => $p->credits,
            'required'          => $p->required,
            'remaining'         => $p->remainingToEarn(),
            'balance'           => round($p->balance, 2),
            'price'             => $price,
            'lastCreditedMonth' => $p->lastCreditedMonth,
            'nextEligibleMonth' => $p->nextEligibleMonth,
        ];
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
    public function previewWith(int $monthsRequired, int $graceDays, string $startDate): array
    {
        $whatIf = new self($monthsRequired, $graceDays, $startDate);

        $ids = [];

        foreach ($whatIf->forClients($whatIf->inScopeClientIds(), false) as $progress) {
            if ($progress->eligible) {
                $ids[] = $progress->clientId;
            }
        }

        return ['count' => count($ids), 'cost' => $this->costOf($ids)];
    }

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
            $progress->nextEligibleMonth = $this->nextMonthFor($progress->credits, $firstOpenMonth ?? null);

            return $progress;
        }

        $today = Carbon::now();

        // Every non-deleted invoice counts for the balance; only what is already due
        // counts as overdue. An invoice issued for next month is not delinquency.
        $todayDate = $today->toDateString();
        foreach ($invoices as $invoice) {
            $remaining = (float) $invoice->remaining_amount;
            if ($remaining <= 0.0) {
                continue;
            }

            $progress->balance += $remaining;

            $due = $this->dateOf($invoice);
            if ($due !== null && $due->toDateString() <= $todayDate) {
                $progress->overdue += $remaining;
            }
        }

        // Boundaries come from subscription months only, one per month.
        $firstOpenMonth = null;
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
                // the month is not over yet — never credited early, but it is still winnable
                $firstOpenMonth ??= $key;

                continue;
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

        // "متأخر" means he owes something that was due — not that a future invoice exists.
        $progress->status = $progress->overdue <= self::EPSILON ? 'clean' : 'late';
        $progress->eligible = $progress->credits >= $this->required
            && $progress->balance <= self::EPSILON
            && ! $progress->hasOpenBonus;
        $progress->nextEligibleMonth = $this->nextMonthFor($progress->credits, $firstOpenMonth);

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
            $hasFlows = false;

            foreach ($flows[$invoice->id] ?? [] as [$timestamp, $amount]) {
                $hasFlows = true;
                if ($timestamp > $limit) {
                    break;
                }
                $paid += $amount;
            }

            // Some invoices carry their settlement in the record itself (imports, and
            // rows written before revenues were logged). Trust it when there is no
            // revenue row at all, otherwise the customer reads as indebted forever.
            if (! $hasFlows && $invoice->paid_date !== null && (float) $invoice->remaining_amount <= self::EPSILON) {
                $paid = Carbon::parse($invoice->paid_date)->timestamp <= $limit ? (float) $invoice->amount : 0.0;
            }

            $total += max(0.0, (float) $invoice->amount - $paid);
        }

        return $total;
    }

    /**
     * The earliest month the customer can still reach the threshold in.
     *
     * A month whose boundary has not passed is still winnable, so the answer must start
     * there — "current month + months left" told a customer with an open boundary he had
     * to wait until the month after it, which is a month later than the truth.
     */
    private function nextMonthFor(int $credits, ?string $firstOpenMonth = null): string
    {
        $needed = max(0, $this->required - $credits);

        if ($firstOpenMonth !== null) {
            $base = Carbon::createFromFormat('Y-m-d', $firstOpenMonth.'-01')->startOfMonth();

            return $base->addMonthsNoOverflow(max(0, $needed - 1))->format('Y-m');
        }

        return Carbon::now()
            ->startOfMonth()
            ->addMonthsNoOverflow($needed)
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
            ->get(['id', 'client_id', 'due_date', 'enshaa_date', 'amount', 'remaining_amount', 'invoice_type', 'paid_date']);

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
