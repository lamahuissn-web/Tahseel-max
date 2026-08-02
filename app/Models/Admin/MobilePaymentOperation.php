<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobilePaymentOperation extends Model
{
    use HasFactory;

    protected $table = 'mobile_payment_operations';

    protected $guarded = [];

    protected $casts = [
        'received_at' => 'datetime',
        'expected_remaining' => 'decimal:2',
        'amount' => 'decimal:2',
        'response_payload' => 'array',
        'receipt_last_attempt_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function revenue()
    {
        return $this->belongsTo(Revenue::class, 'revenue_id');
    }

    public function collector()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'collector_id');
    }
}
