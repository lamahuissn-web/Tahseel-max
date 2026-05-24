<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class HandleMethodNotAllowed
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (MethodNotAllowedHttpException $e) {
            // Handle the exception as needed (e.g., return a custom response)
            return $this->responseApiError('Method not allowed.', 405);
        }
    }
}
