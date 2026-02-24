<?php

use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\User\Brand\BrandController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\AreaController;
use App\Http\Controllers\User\Product\ProductController;
use App\Http\Controllers\User\Shop\ShopController;
use App\Http\Controllers\User\Point\PointController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\Auth\AuthController;
use App\Http\Controllers\User\Auth\ProfileController;
use App\Http\Controllers\User\Basket\BasketController;
use App\Http\Controllers\User\Basket\UserBasketScheduleController;
use App\Http\Controllers\User\MyBasket\MyBasketController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\Category\CategoryController;
use App\Http\Controllers\User\SectionController;
use App\Http\Controllers\User\CityController;
use App\Http\Controllers\User\ComplaintController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\GovernorateController;
use App\Http\Controllers\User\HelpCenterController;
use App\Http\Controllers\User\LegalDocumentController;
use App\Http\Controllers\User\MarketController;
use App\Http\Controllers\User\Order\OrderController;
use App\Http\Controllers\User\Package\SubscriptionController;
use App\Http\Controllers\User\PaymentMethodController;
use App\Http\Controllers\User\Rating\RatingController;
use App\Http\Controllers\User\RecipeController;
use App\Http\Controllers\User\Schedule\ScheduleController;
use App\Http\Controllers\User\SellerRegistrationController;
use App\Http\Controllers\User\Currency\CurrencyController;

Route::prefix('user')->group(
    function () {


        Route::post('/visit-website-bymarkter', [MarketController::class, 'visit']);

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
                    Route::post('/markter-request', [AuthController::class, 'markterRequest']);

                    Route::prefix('/profile')->group(function () {
                        Route::get('/', [ProfileController::class, 'get_profile']);
                        Route::get('/notifications', [ProfileController::class, 'notifications']);

                        Route::post('/update', [ProfileController::class, 'update_profile']);
                        Route::post('/update_password', [ProfileController::class, 'update_password']);

                        Route::post('/update_email', [ProfileController::class, 'update_email']);
                        Route::post('/update_phone', [ProfileController::class, 'update_phone']);
                        Route::post('/verify', [ProfileController::class, 'verify_update']);
                        Route::post('/update-payment-gateway', [ProfileController::class, 'update_payment_gateway']);
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

        // Currency routes
        Route::prefix('currencies')->group(function () {
            Route::get('/', [CurrencyController::class, 'index']);

            Route::middleware('auth:user')->group(function () {
                Route::get('/my-currency', [CurrencyController::class, 'getUserCurrency']);
                Route::post('/update-currency', [CurrencyController::class, 'updateUserCurrency']);
            });
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
        Route::prefix('payment-methods')->group(function () {
            // Public routes
            Route::get('/', [PaymentMethodController::class, 'index']);
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
        Route::apiResource('orders', OrderController::class)->middleware(['auth:user']);

        // Route::apiResource('orders', OrderController::class);
        Route::post('/orders/coupon-preview', [OrderController::class, 'couponPreview'])->middleware(['auth:user']);
        Route::post('/orders/preview', [OrderController::class, 'preview'])->middleware(['auth:user']);

        // Scheduled Baskets - سلال المستخدم المجدولة (CRUD كامل)
        Route::middleware(['auth:user'])->group(function () {
            Route::get('scheduled-baskets', [UserBasketScheduleController::class, 'index'])->name('user.scheduled-baskets.index');
            Route::post('scheduled-baskets', [UserBasketScheduleController::class, 'store'])->name('user.scheduled-baskets.store');
            Route::get('scheduled-baskets/{id}', [UserBasketScheduleController::class, 'show'])->name('user.scheduled-baskets.show');
            Route::put('scheduled-baskets/{id}', [UserBasketScheduleController::class, 'update'])->name('user.scheduled-baskets.update');
            Route::delete('scheduled-baskets/{id}', [UserBasketScheduleController::class, 'destroy'])->name('user.scheduled-baskets.destroy');
        });

        Route::get('/my-baskets', [MyBasketController::class, 'index'])->middleware(['auth:user']);

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
            Route::prefix('points')->group(function () {
                Route::get('/summary', [PointController::class, 'summary']);
                Route::get('/transactions', [PointController::class, 'transactions']);
                Route::get('/statistics', [PointController::class, 'statistics']);
                Route::post('/redeem', [PointController::class, 'redeem']);

                // CRUD operations via BaseCRUDController
                Route::get('/', [PointController::class, 'index']); // List all transactions
                Route::get('/{id}', [PointController::class, 'show']); // Show single transaction

                Route::prefix('exchange')->group(function () {
                    Route::get('/options', [\App\Http\Controllers\User\Point\ExchangeController::class, 'options']);
                    Route::post('/coupon', [\App\Http\Controllers\User\Point\ExchangeController::class, 'exchangeForCoupon']);
                    Route::post('/free-delivery', [\App\Http\Controllers\User\Point\ExchangeController::class, 'exchangeForFreeDelivery']);
                    Route::post('/gift', [\App\Http\Controllers\User\Point\ExchangeController::class, 'exchangeForGift']);
                    Route::get('/history', [\App\Http\Controllers\User\Point\ExchangeController::class, 'history']);
                    Route::get('/active', [\App\Http\Controllers\User\Point\ExchangeController::class, 'activeExchanges']);
                    Route::get('/free-delivery-status', [\App\Http\Controllers\User\Point\ExchangeController::class, 'freeDeliveryStatus']);
                });
            });
        });
        Route::get('/packages', [SubscriptionController::class, 'packages']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
            Route::get('/my-subscription', [SubscriptionController::class, 'mySubscription']);
            Route::post('/renew', [SubscriptionController::class, 'renew']);
        });



        Route::prefix('markter')->middleware(['auth:user'])->group(function () {
            Route::get('/statistics', [MarketController::class, 'statistics']);
            Route::get('/orders', [MarketController::class, 'orders']);
            Route::get('/transactions', [MarketController::class, 'transactions']);
            Route::post('/withdraw-request', [MarketController::class, 'requestWithdraw']);
            Route::get('/withdraw-requests', [MarketController::class, 'withdrawRequests']);
            Route::get('/monthly-orders', [MarketController::class, 'monthlyOrders']);
        });

        Route::prefix('complaints')->middleware(['auth:user'])->group(function () {
            Route::get('/', [ComplaintController::class, 'index']);
            Route::get('/orders', [ComplaintController::class, 'orders']);
            Route::post('/store', [ComplaintController::class, 'store']);
        });
        Route::prefix('legal-documents')->group(function () {
            Route::get('/{key}', [LegalDocumentController::class, 'show']);
        });

        //  Website public routes
        Route::get('faqs', [HelpCenterController::class, 'faqs']);
        Route::get('settings', [HelpCenterController::class, 'settings']);
        Route::post('contactus', [HelpCenterController::class, 'contactus']);
    }
);
