<?php

namespace App\Models\app_setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
class Notification extends Model
{
    use SoftDeletes;
    use HasFactory,HasTranslations;
    public $translatable = ['title','details'];
    protected $table = 'notifications';
    protected $fillable = ['title','details', 'send_to'];
    public $timestamps = true;
}
