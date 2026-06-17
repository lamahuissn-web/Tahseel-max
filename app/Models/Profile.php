<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'tbl_profiles';

    protected $fillable = [
        'name',
        'speed',
        'simultaneous_use',
        'description',
    ];

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Admin\Subscription::class, 'profile_id');
    }

    public function clients()
    {
        return $this->hasMany(Clients::class, 'profile_id');
    }

    public function overriddenClients()
    {
        return $this->hasMany(Clients::class, 'override_profile_id');
    }
}
