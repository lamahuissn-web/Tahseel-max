<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientBonus extends Model
{
    protected $table = 'tbl_client_bonuses';

    protected $guarded = [];

    protected $casts = [
        'earned_at'    => 'date',
        'cycle_start'  => 'date',
        'approved_at'  => 'datetime',
        'redeemed_at'  => 'datetime',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REDEEMED  = 'redeemed';
    public const STATUS_CANCELLED = 'cancelled';

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }
}
