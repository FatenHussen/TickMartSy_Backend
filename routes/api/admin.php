<?php

use App\Http\Controllers\Admin\Admin\AdminCrudController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Banner\BannerCrudController;
use App\Http\Controllers\Admin\Basket\BasketController;
use App\Http\Controllers\Admin\Basket\ScheduledBasketController;
use App\Http\Controllers\Admin\Role_Permission\PermissionIndexController;
use App\Http\Controllers\Admin\Role_Permission\RoleCrudController;
use App\Http\Controllers\Admin\Brand\BrandController;
use App\Http\Controllers\Admin\Category\CategoryAttributeController;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\Driver\DriverCrudController;
use App\Http\Controllers\Admin\Category\CategoryDetailController;
use App\Http\Controllers\Admin\Complaint\ComplaintController;
use App\Http\Controllers\Admin\Governorate\AreaCrudController;
use App\Http\Controllers\Admin\Governorate\CityCrudController;
use App\Http\Controllers\Admin\Governorate\GovernorateCrudController;
use App\Http\Controllers\Admin\Language\LanguageController;
use App\Http\Controllers\Admin\PageSection\PageSectionCrudController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\ProductVariant\ProductVariantController;
use App\Http\Controllers\Admin\ShopProductVariant\ShopProductVariantController;
use App\Http\Controllers\Admin\Section\SectionCrudController;
use App\Http\Controllers\Admin\Section\SectionController;
use App\Http\Controllers\Admin\Service\ServiceCrudController;

use App\Http\Controllers\Admin\Store\StoreCrudController;
use App\Http\Controllers\Admin\Shop\ShopCrudController;
use App\Http\Controllers\Admin\Coupon\CouponCrudController;
use App\Http\Controllers\Admin\LegalDocumentController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Order\OrderController;
use App\Http\Controllers\Admin\User\UserCrudController;
use App\Http\Controllers\Admin\Recipe\RecipeCrudController;
use App\Http\Controllers\Admin\UserBasketSchedule\UserBasketScheduleController;
use App\Http\Controllers\Admin\Vendor\VendorCrudController;
use App\Http\Controllers\Admin\Package\PackageController;
use App\Http\Controllers\Admin\Subscription\SubscriptionController;
use App\Http\Controllers\Admin\Gift\GiftController;
use App\Http\Controllers\Admin\PointExchange\PointExchangeController;
use App\Http\Controllers\Admin\UserPoint\UserPointController;
use App\Http\Controllers\Admin\Currency\CurrencyController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\VendorPackage\VendorPackageController;
use App\Http\Controllers\Admin\VendorSubscription\VendorSubscriptionController;
use App\Http\Controllers\Admin\SellerRegistration\SellerRegistrationCrudController;
use App\Http\Controllers\Admin\VendorUser\VendorUserCrudController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(
    function () {

        Route::apiResource('notifications', NotificationController::class);
        Route::apiResource('legal-documents', LegalDocumentController::class);

        //  Auth routes
        Route::prefix('auth')->group(function () {
            // Public routes
            Route::post('login', [AuthController::class, 'login']);

            // Protected routes
            Route::middleware('auth:admin')->group(function () {
                Route::post('logout', [AuthController::class, 'logout']);
                Route::get('profile', [AuthController::class, 'profile']);
                Route::post('/store-token', [AuthController::class, 'storOrUpdateToken']);
                Route::get('/notifications', [AuthController::class, 'notifications']);
            });
        });
        Route::middleware('auth:admin')->group(function () {
            Route::apiResource('baskets', BasketController::class);
            Route::apiResource('scheduled-baskets', ScheduledBasketController::class);
            Route::apiResource('packages', PackageController::class);
            Route::apiResource('subscriptions', SubscriptionController::class);
            Route::apiResource('gifts', GiftController::class);
            Route::apiResource('point-exchanges', PointExchangeController::class)->only(['index', 'show', 'update']);

            // Vendor Packages & Subscriptions
            Route::apiResource('vendor-packages', VendorPackageController::class);
            Route::apiResource('vendor-subscriptions', VendorSubscriptionController::class);

            // Currency Management
            Route::apiResource('currencies', CurrencyController::class);

            // User Points Management
            Route::prefix('user-points')->group(function () {
                Route::get('/', [UserPointController::class, 'index']);
                Route::get('/{userId}', [UserPointController::class, 'show']);
                Route::get('/{userId}/transactions', [UserPointController::class, 'transactions']);
            });

            // User Basket Schedules (Read-Only)
            Route::prefix('user-basket-schedules')->group(function () {
                Route::get('/', [UserBasketScheduleController::class, 'index']);
                Route::get('/{id}', [UserBasketScheduleController::class, 'get_one']);
            });
        });
        Route::middleware('auth:admin')->group(
            function () {
                Route::resources([
                    'stores'         => StoreCrudController::class,
                    // 'shops'          => ShopCrudController::class,
                    // 'vendors'        => VendorCrudController::class,
                    'languages'      => LanguageController::class,
                    'categories' => CategoryController::class,
                    'brands' => BrandController::class,
                    'category-attributes' => CategoryAttributeController::class,
                    'category-details' => CategoryDetailController::class,
                    'products' => ProductController::class,
                    'product-variants' => ProductVariantController::class,
                    'shop-product-variants' => ShopProductVariantController::class
                ]);
                //     }
                // );
                //  });
                Route::apiResource('shops', ShopCrudController::class);
                // ->middleware('crud.permission:shops');

                Route::apiResource('stores', StoreCrudController::class);
                // ->middleware('crud.permission:stores');

                Route::apiResource('vendors', VendorCrudController::class);

                Route::apiResource('roles', RoleCrudController::class);
                Route::get('permissions', [PermissionIndexController::class, 'index']);

                Route::apiResource('banners', BannerCrudController::class);
                Route::apiResource('admins', AdminCrudController::class);
                Route::apiResource('drivers', DriverCrudController::class);
                Route::apiResource('governorates', GovernorateCrudController::class);
                Route::apiResource('cities', CityCrudController::class);
                Route::apiResource('areas', AreaCrudController::class);
                Route::apiResource('services', ServiceCrudController::class);
                Route::apiResource('sections', SectionCrudController::class);
                Route::apiResource('page-sections', PageSectionCrudController::class);
                Route::apiResource('coupons', CouponCrudController::class);
                Route::apiResource('complaints', ComplaintController::class);
                Route::apiResource('recipes', RecipeCrudController::class);
                Route::apiResource('users', UserCrudController::class);
                Route::apiResource('faqs', FaqController::class);

                // Seller Registration routes
                Route::apiResource('seller-registrations', SellerRegistrationCrudController::class)->only(['index', 'show', 'destroy']);
                Route::post('seller-registrations/{id}/approve', [SellerRegistrationCrudController::class, 'approve']);
                Route::post('seller-registrations/{id}/reject', [SellerRegistrationCrudController::class, 'reject']);

                // Vendor User Management routes (includes shop assignments)
                Route::apiResource('vendor-users', VendorUserCrudController::class);
            }

        );
        //  Auth routes
        Route::prefix('sections')->group(
            function () {
                // Public routes
                Route::get('pages', [SectionController::class, 'pages']);
                Route::get('item-types', [SectionController::class, 'sectionItemTypes']);
                Route::get('display-types', [SectionController::class, 'displayTypes']);

                // Protected routes
                // Basket management routes

                // // Points management routes
                // Route::middleware('auth:admin')->group(function () {
                //     Route::prefix('points')->group(function () {
                //         Route::post('add', [PointController::class, 'addPoints']);
                //         Route::post('deduct', [PointController::class, 'deductPoints']);
                //         Route::get('user-summary', [PointController::class, 'getUserSummary']);
                //         Route::get('user-transactions', [PointController::class, 'getUserTransactions']);
                //     });

                //     Route::apiResource('point-rules', PointRuleController::class);
                // });

                // System Settings routes
                // Route::middleware('auth:admin')->group(function () {
                //     Route::prefix('settings')->group(function () {
                //         Route::get('/', [\App\Http\Controllers\Admin\SystemSettingController::class, 'index']);
                //         Route::get('/group/{group}', [\App\Http\Controllers\Admin\SystemSettingController::class, 'getByGroup']);
                //         Route::post('/batch', [\App\Http\Controllers\Admin\SystemSettingController::class, 'updateBatch']);
                //         Route::post('/clear-cache', [\App\Http\Controllers\Admin\SystemSettingController::class, 'clearCache']);
                //         Route::get('/{key}', [\App\Http\Controllers\Admin\SystemSettingController::class, 'show']);
                //         Route::put('/{key}', [\App\Http\Controllers\Admin\SystemSettingController::class, 'update']);
                //         Route::post('/', [\App\Http\Controllers\Admin\SystemSettingController::class, 'store']);
                //         Route::delete('/{key}', [\App\Http\Controllers\Admin\SystemSettingController::class, 'destroy']);
                //     });
                // });

            }
        );

        Route::prefix('orders')->group(function () {

            Route::get('/', [OrderController::class, 'index']);
            Route::get('{id}/get_one', [OrderController::class, 'get_one']);

            Route::patch('{orderId}/change-status', [OrderController::class, 'changeStatus']);

            Route::post('{orderId}/assign-driver', [OrderController::class, 'assignDriver']);

            Route::patch('items/{itemId}/change-status', [OrderController::class, 'changeItemStatus']);
        });
    }
);
