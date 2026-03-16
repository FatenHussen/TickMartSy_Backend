<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CrudPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $resource)
    {
        $method = $request->method();

        $action = match ($method) {
            'GET'    => $request->route()->hasParameter('id') ? 'view' : 'view',
            'POST'   => 'create',
            'PUT',
            'PATCH'  => 'update',
            'DELETE' => 'delete',
            default  => null,
        };

        if ($action) {
            $permission = "{$resource}.{$action}";
            if (! $request->user('admin')->can($permission)) {
                abort(403, __('custom.Unauthorized'));
            }
        }

        return $next($request);
    }
}
