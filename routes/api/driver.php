<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Driver\Auth\AuthController;
use App\Http\Controllers\Driver\DriverController;
use App\Http\Controllers\Driver\ProfileController;
use App\Http\Controllers\Driver\OrderController;


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

        // Driver management routes
        Route::middleware(['auth:driver'])->group(function () {
            // Status management
            Route::post('/update-status', [DriverController::class, 'updateStatus']);

            // Profile management
            Route::prefix('/profile')->group(function () {
                Route::get('/', [ProfileController::class, 'getProfile']);
                Route::post('/update', [ProfileController::class, 'updateProfile']);
                Route::post('/update-phone', [ProfileController::class, 'updatePhone']);
                Route::post('/verify', [ProfileController::class, 'verifyUpdate']);
            });
        });


        // Route::prefix('orders')->group(function () {

        //     Route::middleware(['auth:driver'])->group(function () {
        //         Route::get('/', [OrderController::class, 'orders']);

        //         Route::get('statistics', [OrderController::class, 'statistics']);

        //         Route::post('update-status/{orderId}', [OrderController::class, 'updateStatus']);
        //         Route::post('accept/{orderId}', [OrderController::class, 'accept']);
        //     });
        // });



        Route::prefix('orders')->group(function () {
            Route::middleware(['auth:driver'])->group(function () {
                Route::get('/', [OrderController::class, 'orders']);
                Route::get('/to-assigned', [OrderController::class, 'ordersToAssigned']);
                Route::get('statistics', [OrderController::class, 'statistics']);
                Route::post('accept/{orderId}', [OrderController::class, 'accept']);
                Route::post('item-out-delivery/{itemId}', [OrderController::class, 'itemOutDelivery']);
                Route::post('order-out-delivery/{orderId}', [OrderController::class, 'orderOutDelivery']);
                Route::post('deliver/{orderId}', [OrderController::class, 'deliver']);
            });
        });
    }
);
