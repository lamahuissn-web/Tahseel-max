<?php

namespace App\Traits;

/**
 * Shared server-side Super-Admin guard for read-only financial API endpoints.
 * The exact production role name is `Super-Admin` (case-sensitive).
 */
trait SuperAdminGuard
{
    protected function isSuperAdmin(): bool
    {
        $user = auth('api')->user();

        return $user !== null && $user->hasRole('Super-Admin');
    }

    protected function superAdminForbidden(string $code): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'result' => false,
            'message' => 'غير مصرح لك بالوصول إلى هذه البيانات.',
            'data' => (object) [],
            'error' => ['code' => $code],
        ], 403);
    }
}
