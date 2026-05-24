<?php

namespace App\Models\Admin;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransfer extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'tbl_account_transfers';

    protected $guarded = [];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
