<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Feature 021 — the loyalty bonus section is for the admin staff and management
 * only (Super-Admin role 1 + accounting role 7). Collectors are refused even if
 * they type the URL by hand.
 */
class EnsureBonusAccess
{
    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user('admin') ?? auth('admin')->user();

        if (! $admin) {
            abort(403);
        }

        $roleIds = $admin->roles->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (array_intersect($roleIds, \App\Services\Bonus\BonusSettings::allowedRoleIds()) === []) {
            abort(403, 'هذا القسم متاح للإدارة فقط');
        }

        return $next($request);
    }
}
