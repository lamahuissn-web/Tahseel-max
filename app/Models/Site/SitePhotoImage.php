<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePhotoImage extends Model
{
    use HasFactory;
    protected $fillable = ['photo_id','image','thumbnailsm','thumbnailmd'];
    public $timestamps = true;
}
