<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SiteTeacher extends Model
{
    use HasFactory,HasTranslations;
    protected $fillable = ['image','email','name','jop_title','description','phone'];
    public $translatable = ['name','jop_title','description'];

    public $timestamps = true;

}
