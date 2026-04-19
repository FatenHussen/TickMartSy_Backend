<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks a Spatie permission name (or several, separated by "|") on the authenticated admin guard.
 */
class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $admin = $request->user('admin');
        if (! $admin) {
            abort(401, __('custom.Unauthorized'));
        }

        $names = array_map('trim', explode('|', $permission));
        foreach ($names as $name) {
            if ($name !== '' && $admin->can($name)) {
                return $next($request);
            }
        }

        abort(403, __('custom.Unauthorized'));
    }
}
