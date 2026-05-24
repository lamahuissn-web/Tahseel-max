<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteEventImage extends Model
{
    use HasFactory;
    protected $fillable = ['event_id','image','thumbnailsm','thumbnailmd'];
    public $timestamps = true;

}
