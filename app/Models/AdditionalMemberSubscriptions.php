<?php

namespace App\Models;

use App\Models\subscriptions\MainSubscription_M;
use App\Models\subscriptions\SpecialSubscription_M;
use App\Models\subscriptions\Transportation_M;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalMemberSubscriptions extends Model
{
    use HasFactory;
    protected $table='tbl_member_additionl_subscriptions';
    protected $guarded=[];

    /******************************************/
    public function main_subscriptions()
    {
        return $this->belongsTo(MainSubscription_M::class, 'subscription_id');
    }
    public function special_subscriptions()
    {
        return $this->belongsTo(SpecialSubscription_M::class, 'subscription_id');
    }

    public function member_subscription()
    {
        return $this->belongsTo(MembersSubscriptions::class, 'member_subscription_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainers::class, 'trainer_id');
    }

    /******************************************************/
    public function member_attendance()
    {
        return $this->hasOne(MembersAttendance::class,'additional_subscription_id','id');
    }

    /*******************************************************/
}
