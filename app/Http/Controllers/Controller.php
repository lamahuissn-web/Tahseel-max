<?php

namespace App\Http\Controllers;

use App\Traits\ImageProcessing;
use App\Traits\MainFunction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests,ImageProcessing,MainFunction;

}
