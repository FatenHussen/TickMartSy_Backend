<?php

namespace App\Filament\Widgets;

use App\Models\VendorFcmToken;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FcmTokenWidget extends Widget
{
    protected string $view = 'filament.widgets.fcm-token-widget';

    public function mount()
    {
        Log::info('FcmTokenWidget mounted');

        $user = Auth::guard('vendor-user')->user();

        if ($user) {
            Log::info('User authenticated', ['vendor_user_id' => $user->id]);

            // توليد token فريد
            $token = 'web_' . $user->id . '_' . time() . '_' . uniqid();

            // تحقق إذا كان التوكن موجود
            $existingToken = VendorFcmToken::where('vendor_user_id', $user->id)
                ->where('fcm_token', $token)
                ->first();

            if (!$existingToken) {
                VendorFcmToken::create([
                    'vendor_user_id' => $user->id,
                    'fcm_token' => $token,
                    'device_type' => 'web',
                ]);

                Log::info('FCM token saved', [
                    'vendor_user_id' => $user->id,
                    'token' => substr($token, 0, 20) . '...',
                ]);
            }
        } else {
            Log::warning('User not authenticated in FcmTokenWidget');
        }
    }
}
