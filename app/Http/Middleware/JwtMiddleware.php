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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = JWTAuth::parseToken();

            if (! $token->check()) {
                return $this->authenticationError('Invalid token', 'authentication_invalid');
            }
        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return $this->authenticationError('Token is Expired', 'authentication_expired');
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return $this->authenticationError('Invalid token', 'authentication_invalid');
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
                return $this->authenticationError('Token not found', 'authentication_required');
            }

            return $this->authenticationError('Unauthorized', 'authentication_invalid');
        }

        return $next($request);
    }

    private function authenticationError(string $message, string $code): Response
    {
        return response()->json([
            'result' => false,
            'message' => $message,
            'data' => (object) [],
            'error' => ['code' => $code],
        ], 401);
    }
}
