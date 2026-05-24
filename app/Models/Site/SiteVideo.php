<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SiteVideo extends Model
{
    use HasFactory,HasTranslations;
    protected $fillable=['main_image','title','link_id','full_link'];
    public $translatable = ['title'];

    public $timestamps = true;

}
