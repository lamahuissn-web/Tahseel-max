<?php

namespace App\Models;

use App\Models\Admin\Account;
use App\Models\Admin\Employee;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Revenue;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

//use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements JWTSubject
{
    //HasRoles
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard = 'admin';

    protected $table = 'admins';


    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'real_password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getImageAttribute($value)
    {
        if (!empty($value)) {
            $image_path = Storage::disk('images')->url($value);
            return asset((Storage::disk('images')->exists($value)) ? $image_path : 'assets/media/avatars/blank.png');
        } else {
            return asset('assets/media/avatars/blank.png');
        }
    }

    // function role()
    // {
    //     return $this->hasOne(Roles::class,'id','group_name');
    // }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }

    public function get_roles()
    {
        $query = DB::table('roles')
            ->select('*');
        $data = $query->get();
        return $data;
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'account_id', 'account_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class, 'collected_by');
    }
}
