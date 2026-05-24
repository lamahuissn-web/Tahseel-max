<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteBlogImage extends Model
{
    use HasFactory;
    protected $fillable = ['blog_id','image','thumbnailsm','thumbnailmd'];
    public $timestamps = true;

}
