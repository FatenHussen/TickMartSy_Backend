<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AttachTokenFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->cookie('auth_token') && !$request->bearerToken()) {
            $request->headers->set(
                'Authorization',
                'Bearer ' . $request->cookie('auth_token')
            );
        }

        return $next($request);
    }
}
