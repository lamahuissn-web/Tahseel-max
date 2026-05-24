<?php

namespace App\Models\Admin;

use App\Models\Admin as AdminModel;
use App\Models\Clients;
use App\Models\ClientsCompanies;
use App\Models\ClientsProjects;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $table = 'tbl_tests';

    protected $fillable = [
        'client_id',
        'company_id',
        'project_id',
        'test_code',
        'talab_number',
        'talab_title',
        'talab_image',
        'talab_date',
        'talab_end_date',
        'created_by',
        'updated_by',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class,'client_id','id');
    }

    public function company()
    {
        return $this->belongsTo(ClientsCompanies::class,'company_id','id');
    }

    public function project()
    {
        return $this->belongsTo(ClientsProjects::class, 'project_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(AdminModel::class, 'created_by');
    }

}
