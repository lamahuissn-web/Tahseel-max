<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = JWTAuth::parseToken();

            if (!$token->check()) {
                return $this->responseApiError('Invalid token', 401);
            }
        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return $this->responseApiError('Token is Expired', 401);
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return $this->responseApiError('Invalid token', 401);
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
                return $this->responseApiError('Token not found', 400);
            }

            return $this->responseApiError('Unauthorized', 401);
        }

        return $next($request);
    }
}
