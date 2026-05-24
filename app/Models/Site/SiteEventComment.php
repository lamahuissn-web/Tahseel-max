<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteEventComment extends Model
{
    use HasFactory;
    protected $fillable = ['event_id','name','email', 'comment','is_read'];
    public $timestamps = true;

}
