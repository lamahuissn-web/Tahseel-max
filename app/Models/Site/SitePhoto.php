<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SitePhoto extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['main_image', 'title', 'details'];
    public $translatable = ['title', 'details'];

    public $timestamps = true;

    public function images()
    {
        return $this->hasMany(SitePhotoImage::class, 'photo_id');
    }
}
