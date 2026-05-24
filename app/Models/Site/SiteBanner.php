<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SiteBanner extends Model
{
    use HasFactory,HasTranslations;
    protected $fillable = ['description','title', 'image','thumbnailsm','thumbnailmd'];
    public $translatable = ['description','title'];

    public $timestamps = true;

}
