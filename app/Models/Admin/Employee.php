<?php

namespace App\Models\Admin;

use App\Models\Admin as AdminModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'tbl_employees';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(AdminModel::class, 'created_by');
    }

    public function data_to_insert($request)
{
    $insert_data['emp_code'] = $request->emp_code;
    $insert_data['first_name'] = $request->first_name;
    $insert_data['last_name'] = $request->last_name;
    // $insert_data['email'] = $request->email;
    // $insert_data['national_id'] = $request->national_id;
    // $insert_data['religion'] = $request->religion;
    $insert_data['phone'] = $request->phone;
    $insert_data['whatsapp_num'] = $request->whatsapp_num;
    $insert_data['address'] = $request->address;
    // $insert_data['date_of_birth'] = $request->date_of_birth;
    // $insert_data['gender'] = $request->gender;
    // $insert_data['material_status'] = $request->material_status;
    $insert_data['position'] = $request->position;
    $insert_data['salary'] = $request->salary;

    return $insert_data;
}
}
