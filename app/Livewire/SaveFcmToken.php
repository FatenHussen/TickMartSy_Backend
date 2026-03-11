<?php

namespace App\Livewire;

use App\Models\VendorFcmToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SaveFcmToken extends Component
{
    public function mount()
    {
        Log::info('SaveFcmToken component mounted');
    }

    public function render()
    {
        return view('livewire.save-fcm-token');
    }

    #[\Livewire\Attributes\On('saveFcmToken')]
    public function saveFcmToken($token)
    {
        Log::info('SaveFcmToken: Received token', ['token' => substr($token, 0, 20) . '...']);

        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            Log::warning('SaveFcmToken: User not authenticated');
            return;
        }

        Log::info('SaveFcmToken: Saving token for user', ['vendor_user_id' => $user->id]);

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

            Log::info('SaveFcmToken: Token saved successfully', ['vendor_user_id' => $user->id]);
        } else {
            Log::info('SaveFcmToken: Token already exists', ['vendor_user_id' => $user->id]);
        }
    }
}
