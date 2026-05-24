<?php

namespace App\Http\Middleware;

//use App\Http\Controllers\Api\ApiResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
//    use ApiResponse;

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
   /* protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }*/


    protected function redirectTo(Request $request)
    {
        if (!$request->expectsJson()) {
           /* if (Auth::guard('guest')->check()) {
                return route('admin.login'); // Redirect to the login route for web guard
            }*/

            if (Auth::guard('admin')->check()) {
                return route('admin.login'); // Redirect to the login route for web guard
            }

           /* if (Auth::guard('api')->check()) {
                return $this->responseApiError('not login', 405);

            }*/
            else{
                return route('admin.login'); // Redirect to the login route for web guard

            }
        }
     /*   if (!$request->expectsJson()) {
            if ((new \Illuminate\Http\Request)->is(app()->getLocale() . '/admin/dashboard')) {
                return route('admin.login'); // Redirect to the login route for web guard
            }
            elseif((new \Illuminate\Http\Request)->is(app()->getLocale() . '/api')) {
                return $this->responseApiError('not login', 405);
            }
            else {
                return route('admin.login'); // Redirect to the login route for web guard
            }
        }*/
    }
}
