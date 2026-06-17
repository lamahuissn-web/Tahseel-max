<?php

namespace App\Models;

use App\Models\Admin\AreaSetting;
use App\Models\Admin\Invoice;
use App\Models\Admin\Subscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clients extends Model
{
    use HasFactory, SoftDeletes;

    protected $table='tbl_clients';
    protected $guarded=[];




    public function scopeLastClientCode($query)
    {
        return $query->orderBy('id', 'desc')->pluck('client_code')->first();
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'client_id');
    }

    public function getLatestInvoiceDueDateAttribute()
    {
        return $this->invoices()
            ->orderBy('created_at', 'desc')
            ->value('due_date');
    }
}
