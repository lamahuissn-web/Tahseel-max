<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SarfBand extends Model
{
    use HasFactory;

    protected $table   ='tbl_sarf_bands';
    protected $guarded = [];

    public function add_sarf_band_data($request)
    {
        $data['title']  = $request->name;

        return $data;
    }

    public function masrofat()
    {
        return $this->hasMany(Masrofat::class, 'band_id');
    }
}
