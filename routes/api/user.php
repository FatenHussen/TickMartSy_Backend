<?php

use App\Http\Controllers\User\Product\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\Auth\AuthController;
use App\Http\Controllers\User\Basket\BasketController;
use App\Http\Controllers\User\Basket\BasketScheduleController;
use App\Http\Controllers\User\Category\CategoryController;
use App\Http\Controllers\User\SectionController;
use App\Http\Controllers\User\CityController;
use App\Http\Controllers\User\GovernorateController;
use App\Http\Controllers\User\RecipeController;

Route::prefix('user')->group(
    function () {
        // public routes 
        Route::get('/governorates', [GovernorateController::class, 'index']);
        Route::get('/cities', [CityController::class, 'index']);

        //authetication routes user 
        Route::prefix('auth')->group(
            function () {
                //public routes
                Route::post('/register', [AuthController::class, 'register']);
                Route::post('/send-otp', [AuthController::class, 'sendOtp']);
                Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
                Route::post('/login', [AuthController::class, 'login']);
                Route::post('/send-password', [AuthController::class, 'sendPassword']);
                Route::post('/verify-password', [AuthController::class, 'verifyPassword']);

                // protected routes 
                Route::middleware(['auth:user'])->group(function () {
                    Route::get('/logout', [AuthController::class, 'logout']);
                    Route::middleware(['auth:user', 'abilities:reset-password'])->group(function () {
                        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
                    });
                });
            }
        );


        //  Section routes
        Route::prefix('sections')->group(function () {
            // Public routes
            Route::get('/', [SectionController::class, 'index']);
        });
        //  Product routes
        Route::prefix('products')->group(function () {
            // Public routes
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/{id}', [ProductController::class, 'get_one']);
        });
        // Category routes
        Route::prefix('categories')->group(function () {
            // Public routes
            Route::get('/', [CategoryController::class, 'index']);
        });
        //  Section routes
        Route::prefix('recipes')->group(function () {
            // Public routes
            Route::get('/', [RecipeController::class, 'index']);
            Route::get('/{id}', [RecipeController::class, 'get_one']);
        });
        Route::prefix('baskets')->group(function () {
            // Public routes
            Route::get('/', [BasketController::class, 'index']);
            Route::get('/{id}', [BasketController::class, 'get_one']);
        });
        Route::prefix('baskets-schedule')->group(function () {
            // Public routes
            Route::get('/', [BasketScheduleController::class, 'index']);
            Route::get('/{id}', [BasketScheduleController::class, 'get_one']);
        });
    }
);
