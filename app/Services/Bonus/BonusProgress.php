<?php

namespace App\Services\Bonus;

class BonusProgress
{
    public int $clientId;
    public int $progress = 0;
    public int $required = 24;
    public string $status = 'compliant';
    public bool $eligible = false;
    public bool $hasOpenBonus = false;
    public int $catchupsUsed = 0;
    public int $carriedArrears = 0;
    public ?string $nextEligibleMonth = null;
    public ?string $cycleStart = null;
    public ?string $lastCreditedMonth = null;

    /** Month (Y-m) of the most recent catch-up, null when none was ever used. */
    public ?string $lastCatchupMonth = null;

    /** Consecutive months, newest first, that were paid inside their own month. */
    public int $cleanMonths = 0;

    /** Did anything break inside this cycle (a late month or a catch-up)? */
    public bool $hadBreak = false;

    /** Did he ever pay after the invoice's own due date in this cycle? */
    public bool $everLateAfterDue = false;

    /** Median delay, in days, between the due date and the payment (null = no payment yet). */
    public ?int $medianLag = null;

    /** Payment habit band: precise | week | late_habit | slow | very_slow. */
    public ?string $pattern = null;

    /** Commitment score 0..100 (45% position + 35% clean months + 20% punctuality). */
    public int $score = 0;

    /** How many more overdue months until each escalation. */
    public int $monthsToSpiral = 0;
    public int $monthsToHardReset = 0;
    public ?string $nextBoundaryDate = null;
    public ?int $daysToNextBoundary = null;

    /** @var array<int,array{month:string,due:?string,state:string,credited:bool,paid_at:?string}> */
    public array $months = [];

    public function toArray(): array
    {
        return [
            'client_id'           => $this->clientId,
            'progress'            => $this->progress,
            'required'            => $this->required,
            'status'              => $this->status,
            'eligible'            => $this->eligible,
            'has_open_bonus'      => $this->hasOpenBonus,
            'catchups_used'       => $this->catchupsUsed,
            'carried_arrears'     => $this->carriedArrears,
            'next_eligible_month' => $this->nextEligibleMonth,
            'cycle_start'         => $this->cycleStart,
            'months'              => $this->months,
        ];
    }
}
