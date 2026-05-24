<?php

namespace App\Models\Admin;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountSettings extends Model
{
    use HasFactory;

    public $table = 'tbl_accounts_settings';
    protected $guarded = [];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function generalAccount()
    {
        return $this->belongsTo(Account::class, 'general_account_id');
    }

    public function masrofatAccount()
    {
        return $this->belongsTo(Account::class, 'masrofat_account_id');
    }

    public function employeeAccount()
    {
        return $this->belongsTo(Account::class, 'employee_account_id');
    }

    public function accountantAccount()
    {
        return $this->belongsTo(Account::class, 'accountant_account_id');
    }
}
