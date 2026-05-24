<?php

namespace App\Models\Site;

use App\Models\Admin\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class SiteData extends Model
{
    use  HasFactory, HasTranslations;

    protected $fillable = ['image', 'name', 'email', 'address', 'fax', 'phone', 'description'
        , 'video', 'maplocation', 'contract_terms', 'discount_ratio', 'commercial_registration_number',
        'tax_number','transport_value','image_print', 'branch_id'];
    public $translatable = ['name', 'address', 'description', 'contract_terms'];

    public function getImageeAttribute($value)
    {
        if (!empty($value)) {
            $image_path = Storage::disk('images')->url($value);

            return asset((Storage::disk('images')->exists($value)) ? $image_path : 'assets/media/svg/files/blank-image-dark.svg');

        } else {
            return asset('assets/media/svg/files/blank-image-dark.svg');

        }
    }

    protected $appends = ['image_print_url'];

    public function getImagePrintUrlAttribute()
    {
        $value = $this->image_print;
        if (!empty($value)) {
            $image_path = Storage::disk('images')->url($value);
            return asset((Storage::disk('images')->exists($value)) ? $image_path : 'assets/media/avatars/blank.png');
        } else {
            return asset('assets/media/avatars/blank.png');

        }
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

}
