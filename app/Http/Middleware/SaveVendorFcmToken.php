<?php

namespace App\Http\Middleware;

use App\Models\VendorFcmToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SaveVendorFcmToken
{
    public function handle(Request $request, Closure $next)
    {
        // احفظ الـ token للـ vendor user إذا كان مسجل دخول
        if (Auth::guard('vendor-user')->check()) {
            try {
                $user = Auth::guard('vendor-user')->user();

                Log::info('SaveVendorFcmToken: User authenticated', ['vendor_user_id' => $user->id]);

                // توليد token فريد
                $token = 'web_' . $user->id . '_' . time() . '_' . uniqid();

                // احفظ الـ token
                VendorFcmToken::create([
                    'vendor_user_id' => $user->id,
                    'fcm_token' => $token,
                    'device_type' => 'web',
                ]);

                Log::info('SaveVendorFcmToken: FCM token saved', [
                    'vendor_user_id' => $user->id,
                    'token' => substr($token, 0, 20) . '...',
                ]);
            } catch (\Exception $e) {
                Log::error('SaveVendorFcmToken: Error', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }
}
