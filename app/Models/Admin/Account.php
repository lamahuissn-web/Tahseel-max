<?php

namespace App\Models\Admin;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'tbl_accounts';

    protected $guarded = [];
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function user()
    {
        return $this->hasOne(Admin::class, 'account_id');
    }

    public function add_account_data($request)
    {
        $data['name'] = $request->input('name');
        $data['parent_id'] = $request->input('parent_id');

        return $data;
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'account_id');
    }

    public function totalAmount()
    {
        $ownAmount = isset($this->financial_transactions_sum_amount)
            ? $this->financial_transactions_sum_amount
            : $this->financialTransactions()->sum('amount');

        $childrenAmount = $this->children()->get()->sum(function ($child) {
            return $child->totalAmount();
        });

        return $ownAmount + $childrenAmount;
    }
}
