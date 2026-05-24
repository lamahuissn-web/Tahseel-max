<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteContact extends Model
{
    use HasFactory;
    protected $fillable=['name','email','phone','subject','title'];
    public $timestamps = true;

}
