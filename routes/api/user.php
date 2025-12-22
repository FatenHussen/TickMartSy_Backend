<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\Auth\AuthController;
use App\Http\Controllers\User\CityController;
use App\Http\Controllers\User\GovernorateController;


Route::prefix('user')->group(
    function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/send-password', [AuthController::class, 'sendPassword']);
    Route::post('/verify-password', [AuthController::class, 'verifyPassword']);
    Route::get('cities', [CityController::class, 'index']);
    Route::get('governorates', [GovernorateController::class, 'index']);
    Route::middleware(['auth:user'])->group(function () {
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::middleware(['auth:user', 'abilities:reset-password'])->group(function () {
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        });
    });
});