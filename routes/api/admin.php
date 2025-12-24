<?php

use App\Http\Controllers\Admin\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(
    function () {

        //  Auth routes
        Route::prefix('auth')->group(function () {
            // Public routes
            Route::post('login', [AuthController::class, 'login']);

            // Protected routes
            Route::middleware('auth:admin')->group(function () {
                Route::post('logout', [AuthController::class, 'logout']);
                Route::get('profile', [AuthController::class, 'profile']);
            });
        });

        Route::prefix('category')->group(function () {
            
        });
        

     });