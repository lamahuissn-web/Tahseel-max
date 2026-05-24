<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Roles extends Model
{
    use HasFactory,HasTranslations;

    protected $fillable = ['title','name','guard_name'];
    public $translatable = ['title'];

    public $timestamps = true;
}
