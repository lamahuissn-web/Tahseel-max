<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;
    protected $table='user_notifications';
    protected $guarded=[];

    public function from_user()
    {
        return $this->belongsTo(User::class,'from_user_id','id');
    }

    /*************************************************************/
    public function from_user_trainer()
    {
        return $this->belongsTo(Trainers::class,'from_user_id','id');
    }
}
