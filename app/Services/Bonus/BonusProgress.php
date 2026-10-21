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
