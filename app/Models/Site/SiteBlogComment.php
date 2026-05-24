<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteBlogComment extends Model
{
    use HasFactory;
    protected $fillable = ['blog_id','name','email', 'comment','is_read'];

    public $timestamps = true;
}
