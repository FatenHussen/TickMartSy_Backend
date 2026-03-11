<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorFcmToken;
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
            'device_name' => 'nullable|string',
            'device_type' => 'nullable|string|in:web,mobile,tablet',
        ]);

        $user = Auth::guard('vendor-user')->user();

        if (!$user || !$user->vendor_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // تحقق إذا كانت موجودة
        $token = VendorFcmToken::where('vendor_id', $user->vendor_id)
            ->where('fcm_token', $validated['fcm_token'])
            ->first();

        if (!$token) {
            VendorFcmToken::create([
                'vendor_id' => $user->vendor_id,
                'fcm_token' => $validated['fcm_token'],
                'device_name' => $validated['device_name'] ?? null,
                'device_type' => $validated['device_type'] ?? 'web',
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

        if (!$user || !$user->vendor_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        VendorFcmToken::where('vendor_id', $user->vendor_id)
            ->where('fcm_token', $validated['fcm_token'])
            ->delete();

        return response()->json(['success' => true]);
    }
}
