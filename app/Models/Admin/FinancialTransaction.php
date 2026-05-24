<?php

namespace App\Models\Admin;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'tbl_financial_transactions';

    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
