<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\FcmController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\CityController;
use App\Http\Controllers\User\GovernorateController;

require base_path('routes/api/admin.php');


Route::group(["middleware" => ['setLocale']], function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/send_otp', [AuthController::class, 'send_otp']);
    Route::post('/verify_otp', [AuthController::class, 'verify_otp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/send_password', [AuthController::class, 'send_password']);
    Route::post('/verify_password', [AuthController::class, 'verify_password']);
    Route::get('cities', [CityController::class, 'index']);
    Route::get('governorates', [GovernorateController::class, 'index']);
    Route::middleware(['auth:sanctum'])->group(function () {   
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::middleware(['auth:sanctum', 'abilities:reset-password'])->group(function () {
            Route::post('/reset_password', [AuthController::class, 'reset_password']);
        });
    });
});