<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SiteBlog extends Model
{
    use HasFactory,HasTranslations;
    protected $fillable = ['details','title','date_at','publisher', 'main_image'];
    public $translatable = ['details','title'];

    public $timestamps = true;

    public function images()
    {
        return $this->hasMany(SiteBlogImage::class,'blog_id');
    }

}
