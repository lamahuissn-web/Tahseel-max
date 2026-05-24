<?php

namespace App\Models\Admin;

use App\Models\Admin as AdminModel;
use App\Models\Clients;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revenue extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'tbl_revenues';
    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(AdminModel::class, 'collected_by');
    }

    public function getCollectedByNameAttribute()
    {
        $admin = AdminModel::where('emp_id', $this->collected_by)->first();
        if ($admin) {
            return $admin->name;
        }

        $employee = Employee::find($this->collected_by);
        return $employee ? $employee->first_name.' '.$employee->last_name : 'Unknown';
    }

}
