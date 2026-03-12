<?php

use App\Http\Controllers\VendorFcmTokenController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorFcmToken;

Route::middleware(['auth:vendor-user', 'api'])->group(function () {
    // Route::post('/fcm-token', [VendorFcmTokenController::class, 'store']);
    Route::delete('/fcm-token', [VendorFcmTokenController::class, 'destroy']);
});
// Route::post('/vendor/fcm-token', function (Request $request) {

//     $user = Auth::guard('vendor-user')->user();

//     if (!$user) {
//         return response()->json(['error' => 'Unauthenticated'], 401);
//     }

//     VendorFcmToken::updateOrCreate(
//         [
//             'vendor_user_id' => $user->id,
//             'fcm_token' => $request->token
//         ],
//         [
//             'device_type' => 'web'
//         ]
//     );

//     return response()->json(['success' => true]);
// });
