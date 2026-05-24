<?php

namespace App\Models\setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class main_setting extends Model
{
    use HasFactory,HasTranslations;

    protected $table = 'main_category';
    protected $fillable = ['name', 'image','description'];
    public $translatable = ['name','description'];


}
