<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $table   ='tbl_branches';
    protected $fillable = [
        'name', 'area_setting_id', 'address', 'phone', 'color', 'site_data_id'
    ];

    public function areaSetting()
    {
        return $this->belongsTo(AreaSetting::class);
    }

    public function add_branch_data($request)
    {
        $data['name']  =$request->name;

        return $data;
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
