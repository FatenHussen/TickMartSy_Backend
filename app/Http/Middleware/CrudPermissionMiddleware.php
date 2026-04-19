<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Maps HTTP verbs to Spatie CRUD-style permissions: {resource}.{view|create|update|delete}.
 * The resource segment is resolved via config/admin-permissions.php when using plural / kebab route names.
 */
class CrudPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $admin = $request->user('admin');
        if (! $admin) {
            abort(401, __('custom.Unauthorized'));
        }

        /** @var array<string, string> $aliases */
        $aliases = config('admin-permissions.crud_resource_prefix', []);
        $prefix = $aliases[$resource] ?? $resource;

        $method = $request->method();
        $action = match ($method) {
            'GET' => 'view',
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => null,
        };

        if ($action === null) {
            return $next($request);
        }

        $permission = "{$prefix}.{$action}";
        if (! $admin->can($permission)) {
            abort(403, __('custom.Unauthorized'));
        }

        return $next($request);
    }
}
