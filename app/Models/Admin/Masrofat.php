<?php

namespace App\Models\Admin;

use App\Models\Admin as AdminModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Masrofat extends Model
{
    use HasFactory;

    protected $table   ='tbl_masrofat';
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }

    public function sarf_band()
    {
        return $this->belongsTo(SarfBand::class, 'band_id');
    }

    public function user()
    {
        return $this->belongsTo(AdminModel::class, 'created_by');
    }
}
