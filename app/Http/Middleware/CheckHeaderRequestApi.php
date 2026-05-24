<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckHeaderRequestApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
/*--------------------check lang & set as defult ------------------------*/
        if ($request->headers->has('lang')) {
            $locale = $request->header('lang');
        } else {
            $locale = app()->getLocale();
        }
        app()->setLocale($locale);

        return $next($request);
    }
}
