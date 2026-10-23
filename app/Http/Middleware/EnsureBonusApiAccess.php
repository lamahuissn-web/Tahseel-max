<?php

namespace App\Http\Middleware;

use App\Services\Bonus\BonusSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Feature 021 — the bonus section in the mobile app is administration only.
 * Hidden menus are not security: the server answers 403 for every other role.
 */
class EnsureBonusApiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (! $user) {
            return $this->deny('الجلسة منتهية — سجّل الدخول من جديد.', 'authentication_required');
        }

        $roleId = (int) ($user->roles()->first()?->id ?? 0);

        if (! in_array($roleId, BonusSettings::allowedRoleIds(), true)) {
            return $this->deny('هذا القسم مخصّص للإدارة.', 'bonus_forbidden');
        }

        return $next($request);
    }

    private function deny(string $message, string $code): Response
    {
        return response()->json([
            'result'  => false,
            'message' => $message,
            'data'    => (object) [],
            'error'   => ['code' => $code],
        ], 403);
    }
}
