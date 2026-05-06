<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VendorFcmTokenController extends Controller
{
    /**
     * حفظ FCM token من المتصفح
     */
    public function store(Request $request)
    {
        Log::info('VendorFcmTokenController@store called', [
            'user' => Auth::guard('vendor-user')->user()?->id,
            'request_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'token' => 'required|string',
            'device_id' => 'nullable|string',
        ]);

        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            Log::warning('Unauthorized attempt to save FCM token');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('Saving FCM token for vendor user', [
            'vendor_user_id' => $user->id,
            'token' => substr($validated['token'], 0, 20) . '...',
        ]);

        // تحقق إذا كان التوكن موجود
        $existingToken = $user->fcmTokens()
            ->where('fcm_token', $validated['token'])
            ->first();

        if (!$existingToken) {
            $token = $user->fcmTokens()->create([
                'fcm_token' => $validated['token'],
                'device_id' => $validated['device_id'] ?? 'web',
            ]);

            Log::info('FCM token saved successfully', [
                'token_id' => $token->id,
                'vendor_user_id' => $user->id,
            ]);
        } else {
            Log::info('FCM token already exists', [
                'vendor_user_id' => $user->id,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Token saved successfully']);
    }

    /**
     * حذف FCM token
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->fcmTokens()
            ->where('fcm_token', $validated['token'])
            ->delete();

        return response()->json(['success' => true, 'message' => 'Token deleted successfully']);
    }
}
