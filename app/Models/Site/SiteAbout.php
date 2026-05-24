<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SiteAbout extends Model
{
    use HasFactory;
    use HasFactory,HasTranslations;
    public $translatable = ['address','sub_address','details'];
    protected $fillable = ['address', 'sub_address', 'details', 'image'];
    public $timestamps = true;
}
