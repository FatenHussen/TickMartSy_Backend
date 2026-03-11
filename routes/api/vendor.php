<?php

use App\Http\Controllers\VendorFcmTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:vendor-user')->group(function () {
    Route::post('/fcm-token', [VendorFcmTokenController::class, 'store']);
    Route::delete('/fcm-token', [VendorFcmTokenController::class, 'destroy']);
});
