<?php

use App\Http\Controllers\User\Brand\BrandController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\AreaController;
use App\Http\Controllers\User\Product\ProductController;
use App\Http\Controllers\User\Shop\ShopController;
use App\Http\Controllers\User\PointController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\Auth\AuthController;
use App\Http\Controllers\User\Auth\ProfileController;
use App\Http\Controllers\User\Basket\BasketController;
use App\Http\Controllers\User\Basket\BasketScheduleController;
use App\Http\Controllers\User\Basket\UserBasketScheduleController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\Category\CategoryController;
use App\Http\Controllers\User\SectionController;
use App\Http\Controllers\User\CityController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\GovernorateController;
use App\Http\Controllers\User\Order\OrderController;
use App\Http\Controllers\User\Rating\RatingController;
use App\Http\Controllers\User\RecipeController;
use App\Http\Controllers\User\Schedule\ScheduleController;
use App\Http\Controllers\User\SellerRegistrationController;

Route::prefix('user')->group(
    function () {

        // public routes 
        Route::get('/governorates', [GovernorateController::class, 'index']);
        Route::get('/cities', [CityController::class, 'index']);
        Route::get('/areas', [AreaController::class, 'index']);

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
                Route::post('/seller-register', [SellerRegistrationController::class, 'store']);

                // protected routes 
                Route::middleware(['auth:user'])->group(function () {
                    Route::get('/logout', [AuthController::class, 'logout']);
                    Route::post('/store-token', [AuthController::class, 'storOrUpdateToken']);

                    Route::prefix('/profile')->group(function () {
                        Route::get('/', [ProfileController::class, 'get_profile']);
                        Route::post('/update', [ProfileController::class, 'update_profile']);
                        Route::post('/update_password', [ProfileController::class, 'update_password']);

                        Route::post('/update_email', [ProfileController::class, 'update_email']);
                        Route::post('/update_phone', [ProfileController::class, 'update_phone']);
                        Route::post('/verify', [ProfileController::class, 'verify_update']);
                    });
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

        Route::prefix('schedules')->group(function () {
            // Public routes
            Route::get('/', [ScheduleController::class, 'index']);
        });

        //  Product routes
        Route::prefix('products')->group(function () {
            // Public routes
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/{id}', [ProductController::class, 'get_one']);
        });

        // Shop routes
        Route::prefix('shops')->group(function () {
            // Public routes
            Route::get('/', [ShopController::class, 'index']);
            Route::get('/{id}', [ShopController::class, 'get_one']);
        });


        // Category routes
        Route::prefix('categories')->group(function () {
            // Public routes
            Route::get('/', [CategoryController::class, 'index']);
        });

        // Brand routes
        Route::prefix('brands')->group(function () {
            // Public routes
            Route::get('/', [BrandController::class, 'index']);
            Route::get('/{id}', [BrandController::class, 'get_one']);
        });

        // Recipe routes
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

        Route::prefix('ratings')->group(function () {
            // Public routes
            Route::get('/', [RatingController::class, 'index']);
            Route::middleware(['auth:user'])->group(function () {
                Route::get('/my_ratings', [RatingController::class, 'myRatings']);
                Route::post('/', [RatingController::class, 'store']);
                Route::put('/{id}', [RatingController::class, 'update']);
                Route::delete('/{id}', [RatingController::class, 'destroy']);
            });
        });
        // Route::apiResource('orders', OrderController::class)->middleware(['auth:user']);

        Route::apiResource('orders', OrderController::class);
        Route::post('/orders/coupon-preview', [OrderController::class, 'couponPreview'])->middleware(['auth:user']);
        Route::post('/orders/preview', [OrderController::class, 'preview'])->middleware(['auth:user']);

        Route::apiResource('scheduled-baskets', UserBasketScheduleController::class)->middleware(['auth:user']);

        Route::apiResource('addresses', AddressController::class)->middleware(['auth:user']);

        Route::prefix('cart')->group(function () {
            Route::middleware(['auth:user'])->group(function () {
                Route::post('calculate-delivery-price', [CartController::class, 'calculateDeliveryPrice']);
            });
        });

        Route::prefix('favorites')->group(function () {
            Route::middleware(['auth:user'])->group(function () {
                Route::get('/', [FavoriteController::class, 'index']);
                Route::post('/toggle', [FavoriteController::class, 'toggle']);
            });
        });

        // Points routes
        Route::middleware(['auth:user'])->group(function () {
            // Route::prefix('points')->group(function () {
            //     Route::get('/summary', [PointController::class, 'summary']);
            //     Route::get('/transactions', [PointController::class, 'transactions']);
            //     Route::get('/statistics', [PointController::class, 'statistics']);
            //     Route::post('/redeem', [PointController::class, 'redeem']);

            //     // CRUD operations via BaseCRUDController
            //     Route::get('/', [PointController::class, 'index']); // List all transactions
            //     Route::get('/{id}', [PointController::class, 'show']); // Show single transaction
            // });
        });
    }
);
