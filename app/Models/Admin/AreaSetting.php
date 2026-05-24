<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaSetting extends Model
{
    use HasFactory;

    protected $table   ='tbl_area_settings';
    protected $fillable = [
        'title','parent_id'
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function add_governorate_data($request)
    {
        $data['title']  =$request->title;

        return $data;
    }

    public function add_area_data($request)
    {
        $data['title'] = $request->title;
        $data['parent_id'] = $request->parent_id;

        return $data;
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
