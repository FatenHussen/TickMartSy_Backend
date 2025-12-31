<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Driver\Auth\AuthController;


Route::prefix('driver')->group(
    function () {
        Route::prefix('auth')->group(
            function () {
                //public routes
                Route::post('/login', [AuthController::class, 'login']);
                Route::post('/send-password', [AuthController::class, 'sendPassword']);
                Route::post('/verify-password', [AuthController::class, 'verifyPassword']);

                // protected routes 
                Route::middleware(['auth:driver'])->group(function () {
                    Route::get('/logout', [AuthController::class, 'logout']);
                    Route::middleware(['auth:driver', 'abilities:reset-password'])->group(function () {
                        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
                    });
                });
            }
        );
    }
);
