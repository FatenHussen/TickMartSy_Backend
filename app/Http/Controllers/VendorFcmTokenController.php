<?php

namespace App\Http\Controllers;

use App\Models\VendorFcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorFcmTokenController extends Controller
{
    /**
     * حفظ FCM token من المتصفح
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // تحقق إذا كان التوكن موجود
        $existingToken = VendorFcmToken::where('vendor_user_id', $user->id)
            ->where('fcm_token', $validated['token'])
            ->first();

        if (!$existingToken) {
            VendorFcmToken::create([
                'vendor_user_id' => $user->id,
                'fcm_token' => $validated['token'],
                'device_type' => 'web',
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

        VendorFcmToken::where('vendor_user_id', $user->id)
            ->where('fcm_token', $validated['token'])
            ->delete();

        return response()->json(['success' => true, 'message' => 'Token deleted successfully']);
    }
}
