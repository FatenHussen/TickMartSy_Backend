<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\Auth\AuthController;
use App\Http\Controllers\User\CityController;
use App\Http\Controllers\User\GovernorateController;

Route::group(["middleware" => ['setLocale']], function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/send_otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify_otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/send_password', [AuthController::class, 'sendPassword']);
    Route::post('/verify_password', [AuthController::class, 'verifyPassword']);
    Route::get('cities', [CityController::class, 'index']);
    Route::get('governorates', [GovernorateController::class, 'index']);
    Route::middleware(['auth:sanctum'])->group(function () {   
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::middleware(['auth:sanctum', 'abilities:reset-password'])->group(function () {
            Route::post('/reset_password', [AuthController::class, 'resetPassword']);
        });
    });
});