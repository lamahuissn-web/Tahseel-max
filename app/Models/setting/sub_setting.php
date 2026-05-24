<?php

namespace App\Models\setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class sub_setting extends Model
{
    use HasFactory,HasTranslations;
    protected $table = 'sub_settings';
    protected $fillable = ['name', 'image','main_category','description'];
    public $translatable = ['name','description'];
}
