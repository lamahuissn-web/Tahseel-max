<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SiteEvent extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['details', 'title', 'date_at', 'publisher', 'main_image','from_time','to_time','location','location_map'];
    public $translatable = ['details', 'title','location'];

    public $timestamps = true;

    public function images()
    {
        return $this->hasMany(SiteEventImage::class,'event_id');
    }
}
