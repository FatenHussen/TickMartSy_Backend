<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorFcmTokenController extends Controller
{
    /**
     * Store FCM token for vendor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'device_id' => 'nullable|string',
        ]);

        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = $user->fcmTokens()
            ->where('fcm_token', $validated['fcm_token'])
            ->first();

        if (!$token) {
            $user->fcmTokens()->create([
                'fcm_token' => $validated['fcm_token'],
                'device_id' => $validated['device_id'] ?? 'web',
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove FCM token
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->fcmTokens()
            ->where('fcm_token', $validated['fcm_token'])
            ->delete();

        return response()->json(['success' => true]);
    }
}
