<?php
namespace App\Models\app_setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
class Discount extends Model
{  use SoftDeletes;
    use HasFactory;
    protected $table = 'discounts';
    public $translatable = ['name'];
    protected $fillable = [
        'code',
        'percentage',
        'amount',
        'max_limit',
        'start_date',
        'end_date',
        'name',
        'type',
    ];
}
