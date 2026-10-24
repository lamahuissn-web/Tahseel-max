<?php

namespace App\Services\Bonus;

/**
 * Feature 022 — the simplified progress of one customer.
 *
 * Two states only: منتظم (no outstanding balance today) or متأخر.
 * No score, no habit band, no spiral, no hard reset, no catch-up.
 */
class SimpleBonusProgress
{
    public int $clientId = 0;

    /** Months credited since the start date (or since the last issued free month). */
    public int $credits = 0;

    /** The owner-configured threshold at the moment of reading. */
    public int $required = 12;

    /** Total outstanding balance today (every invoice, services included). */
    public float $balance = 0.0;

    public bool $eligible = false;

    public bool $hasOpenBonus = false;

    /** `Y-m` of the most recent credited month, null when none. */
    public ?string $lastCreditedMonth = null;

    /** `Y-m` the customer is expected to reach the threshold. */
    public ?string $nextEligibleMonth = null;

    /** منتظم | متأخر — describes TODAY, not history. */
    public string $status = 'late';

    /** @var array<int,array{month:string,due:string,credited:bool,balance:float,paid_at:?string}> */
    public array $months = [];

    public function statusLabel(): string
    {
        return $this->status === 'clean' ? 'منتظم' : 'متأخر';
    }

    public function remainingToEarn(): int
    {
        return max(0, $this->required - $this->credits);
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'clientId'          => $this->clientId,
            'credits'           => $this->credits,
            'required'          => $this->required,
            'remaining'         => $this->remainingToEarn(),
            'balance'           => round($this->balance, 2),
            'eligible'          => $this->eligible,
            'hasOpenBonus'      => $this->hasOpenBonus,
            'lastCreditedMonth' => $this->lastCreditedMonth,
            'nextEligibleMonth' => $this->nextEligibleMonth,
            'status'            => $this->status,
            'statusLabel'       => $this->statusLabel(),
            'months'            => $this->months,
        ];
    }
}
