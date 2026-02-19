<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FilamentAutoLogout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if response is 401 Unauthorized
        if ($response->getStatusCode() === 401) {
            // Check if this is a Filament vendor panel request
            if ($request->is('vendor/*')) {
                // Logout the vendor user
                Auth::guard('vendor-user')->logout();

                // Invalidate session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect to login page
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Unauthenticated.',
                        'redirect' => route('filament.vendor.auth.login')
                    ], 401);
                }

                return redirect()->route('filament.vendor.auth.login');
            }
        }

        return $response;
    }
}
