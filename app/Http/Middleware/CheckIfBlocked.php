<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CheckIfBlocked
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user(); 

        if ($user && $user->is_block) {
            return response()->json([
                'message' => __('custom.account_blocked')  
            ], 403);
        }

        return $next($request);
    }
}
