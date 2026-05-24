<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFiles extends Model
{
    use HasFactory;
    protected $table   ='tbl_employee_files';
    protected $fillable = [
        'emp_id','file_name','file','publisher','publisher_n'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }



}
