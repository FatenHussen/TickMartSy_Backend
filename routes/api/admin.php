<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Admin\AdminCrudController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\Banner\BannerCrudController;
use App\Http\Controllers\Admin\Basket\BasketController;
use App\Http\Controllers\Admin\Basket\ScheduledBasketController;
use App\Http\Controllers\Admin\AffiliateWithdrawRequest\AffiliateWithdrawRequestController;
use App\Http\Controllers\Admin\AffiliateWalletTransaction\AffiliateWalletTransactionController;
use App\Http\Controllers\Admin\Role_Permission\PermissionIndexController;
use App\Http\Controllers\Admin\Role_Permission\RoleCrudController;
use App\Http\Controllers\Admin\Brand\BrandController;
use App\Http\Controllers\Admin\Category\CategoryAttributeController;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\Color\ColorController;
use App\Http\Controllers\Admin\Driver\DriverCrudController;
use App\Http\Controllers\Admin\Category\CategoryDetailController;
use App\Http\Controllers\Admin\Complaint\ComplaintController;
use App\Http\Contffrollers\Admin\Governorate\AreaCrudController;
use App\Http\Controllers\Admin\Governorate\CityCrudController;
use App\Http\Controllers\Admin\Governorate\GovernorateCrudController;
use App\Http\Controllers\Admin\Country\CountryCrudController;
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
use App\Http\Controllers\Admin\VendorAccounting\VendorAccountingController;
use App\Http\Controllers\Admin\Package\PackageController;
use App\Http\Controllers\Admin\Subscription\SubscriptionController;
use App\Http\Controllers\Admin\Gift\GiftController;
use App\Http\Controllers\Admin\UserGift\UserGiftController as AdminUserGiftController;
use App\Http\Controllers\Admin\PointExchange\PointExchangeController;
use App\Http\Controllers\Admin\UserPoint\UserPointController;
use App\Http\Controllers\Admin\Currency\CurrencyController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\PopupCampaignController;
use App\Http\Controllers\Admin\Governorate\AreaCrudController as GovernorateAreaCrudController;
use App\Http\Controllers\Admin\VendorPackage\VendorPackageController;
use App\Http\Controllers\Admin\VendorWithdrawRequest\VendorWithdrawRequestController;
use App\Http\Controllers\Admin\VendorSubscription\VendorSubscriptionController;
use App\Http\Controllers\Admin\SellerRegistration\SellerRegistrationCrudController;
use App\Http\Controllers\Admin\VendorUser\VendorUserCrudController;
use App\Http\Controllers\Admin\Schedule\ScheduleCrudController;
use App\Http\Controllers\Admin\Statistics\StatisticsController;
use App\Http\Controllers\Admin\Reports\ReportsController;
use App\Http\Controllers\Admin\PointRuleController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\Setting\SettingController;
use App\Http\Controllers\Admin\SystemSettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QuickActionController;
use App\Http\Controllers\Admin\ServiceOrder\ServiceOrderController;

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
                Route::post('/store-token', [AuthController::class, 'storOrUpdateToken']);
                Route::get('/notifications', [AuthController::class, 'notifications']);
            });
        });
        Route::middleware('auth:admin')->group(function () {
            Route::get('/activity-logs', [ActivityLogController::class, 'index']);
            Route::apiResource('notifications', NotificationController::class);
            Route::apiResource('legal-documents', LegalDocumentController::class);

            // Toggle Status API - Universal endpoint for toggling is_active
            Route::post('/toggle-status', [\App\Http\Controllers\Admin\ToggleStatusController::class, 'toggleStatus']);

            Route::apiResource('baskets', BasketController::class);
            Route::apiResource('scheduled-baskets',  ScheduledBasketController::class);
            Route::apiResource('schedules', ScheduleCrudController::class);
            Route::apiResource('packages', PackageController::class);
            Route::apiResource('subscriptions', SubscriptionController::class);
            Route::post('gifts/bulk', [GiftController::class, 'bulkStore']);
            Route::apiResource('gifts', GiftController::class);
            Route::apiResource('user-gifts', AdminUserGiftController::class);
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

            // Point Rules Management
            Route::apiResource('point-rules', PointRuleController::class);

            // Icons Management
            Route::apiResource('icons', \App\Http\Controllers\Admin\IconController::class);
            Route::apiResource('quick-actions', QuickActionController::class);

            // Statistics & Reports
            Route::prefix('statistics')->group(function () {
                Route::get('/dashboard', [StatisticsController::class, 'dashboard']);
                Route::get('/counts', [StatisticsController::class, 'counts']);
                Route::get('/monthly-performance', [StatisticsController::class, 'monthlyPerformance']);
                Route::get('/orders-by-status', [StatisticsController::class, 'ordersByStatus']);
                Route::get('/top-shops', [StatisticsController::class, 'topShops']);

                // Chart-specific endpoints
                Route::get('/revenue-trend', [StatisticsController::class, 'revenueTrend']);
                Route::get('/orders-by-hour', [StatisticsController::class, 'ordersByHour']);
                Route::get('/orders-by-day', [StatisticsController::class, 'ordersByDayOfWeek']);
                Route::get('/revenue-by-payment', [StatisticsController::class, 'revenueByPaymentMethod']);
                Route::get('/top-categories', [StatisticsController::class, 'topCategoriesByRevenue']);
                Route::get('/user-growth', [StatisticsController::class, 'userGrowth']);
                Route::get('/order-funnel', [StatisticsController::class, 'orderStatusFunnel']);
                Route::get('/avg-order-value-trend', [StatisticsController::class, 'averageOrderValueTrend']);
                Route::get('/driver-comparison', [StatisticsController::class, 'driverPerformanceComparison']);
                Route::get('/stock-levels', [StatisticsController::class, 'productStockLevels']);
                Route::get('/sales-heatmap', [StatisticsController::class, 'salesHeatmap']);
            });

            Route::prefix('reports')->group(function () {
                Route::get('/sales', [ReportsController::class, 'sales']);
                Route::get('/product-movement', [ReportsController::class, 'productMovement']);
                Route::get('/vendor-performance/{vendorId}', [ReportsController::class, 'vendorPerformance']);
                Route::get('/driver-performance/{driverId}', [ReportsController::class, 'driverPerformance']);
                Route::get('/sales-by-location', [ReportsController::class, 'salesByLocation']);
                Route::get('/sales-by-category', [ReportsController::class, 'salesByCategory']);

                // Export endpoints
                Route::get('/export/sales', [ReportsController::class, 'exportSales']);
                Route::get('/export/product-movement', [ReportsController::class, 'exportProductMovement']);
                Route::get('/export/vendor-performance/{vendorId}', [ReportsController::class, 'exportVendorPerformance']);
                Route::get('/export/driver-performance/{driverId}', [ReportsController::class, 'exportDriverPerformance']);
            });

            // Settings routes (Setting model)
            Route::prefix('settings')->group(function () {
                Route::get('/', [SettingController::class, 'index']);
                Route::get('/{key}', [SettingController::class, 'show']);
                Route::put('/{key}', [SettingController::class, 'update']);
            });

            // System Settings routes
            Route::prefix('system-settings')->group(function () {
                Route::get('/', [SystemSettingController::class, 'index']);
                Route::get('/group/{group}', [SystemSettingController::class, 'getByGroup']);
                Route::post('/batch', [SystemSettingController::class, 'updateBatch']);
                Route::post('/clear-cache', [SystemSettingController::class, 'clearCache']);
                Route::get('/{key}', [SystemSettingController::class, 'show']);
                Route::put('/{key}', [SystemSettingController::class, 'update']);
                Route::post('/', [SystemSettingController::class, 'store']);
                Route::delete('/{key}', [SystemSettingController::class, 'destroy']);
            });

            // User Basket Schedules (Read-Only)
            Route::prefix('user-basket-schedules')->group(function () {
                Route::get('/', [UserBasketScheduleController::class, 'index']);
                Route::get('/{id}', [UserBasketScheduleController::class, 'get_one']);
                Route::post('/{id}/disable', [UserBasketScheduleController::class, 'disable']);
                Route::post('/{id}/enable', [UserBasketScheduleController::class, 'enable']);
            });

            Route::prefix('sections')->group(
                function () {
                    // Public routes
                    Route::get('pages', [SectionController::class, 'pages']);
                    Route::get('item-types', [SectionController::class, 'sectionItemTypes']);
                    Route::get('display-types', [SectionController::class, 'displayTypes']);
                }
            );
        });
        Route::middleware('auth:admin')->group(
            function () {

                // Route::get('page-sections/display-types/{manual_model}', [PageSectionCrudController::class, 'displayTypes']);

                Route::resources([
                    'stores'         => StoreCrudController::class,
                    // 'shops'          => ShopCrudController::class,
                    // 'vendors'        => VendorCrudController::class,
                    'languages'      => LanguageController::class,
                    'categories' => CategoryController::class,
                    'brands' => BrandController::class,
                    'colors' => ColorController::class,
                    'category-attributes' => CategoryAttributeController::class,
                    'category-details' => CategoryDetailController::class,
                    'products' => ProductController::class,
                    'product-variants' => ProductVariantController::class,
                    'shop-product-variants' => ShopProductVariantController::class,
                    'sale-countries' => \App\Http\Controllers\Admin\SaleCountry\SaleCountryCrudController::class,
                ]);

                // Product Approval routes
                Route::post('products/{id}/approve', [ProductController::class, 'approve']);
                Route::post('products/{id}/reject', [ProductController::class, 'reject']);

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
                Route::apiResource('areas', GovernorateAreaCrudController::class);
                Route::apiResource('countries', CountryCrudController::class);
                Route::apiResource('services', ServiceCrudController::class);
                Route::apiResource('vendor-service-types', \App\Http\Controllers\Admin\VendorServiceType\VendorServiceTypeCrudController::class);
                Route::apiResource('vendor-services', \App\Http\Controllers\Admin\VendorService\VendorServiceCrudController::class);
                Route::apiResource('shop-vendor-services', \App\Http\Controllers\Admin\ShopVendorService\ShopVendorServiceCrudController::class);
                Route::apiResource('sections', SectionCrudController::class);
                Route::apiResource('page-sections', PageSectionCrudController::class);
                Route::apiResource('coupons', CouponCrudController::class);
                Route::apiResource('complaints', ComplaintController::class);
                Route::apiResource('recipes', RecipeCrudController::class);
                Route::apiResource('faqs', FaqController::class);
                Route::apiResource('popup-campaigns', PopupCampaignController::class);
                Route::apiResource('badges', BadgeController::class);
                Route::apiResource('promotions', PromotionController::class);
                Route::apiResource('flash-sales', FlashSaleController::class)->only(['index','store', 'update']);
                Route::apiResource('affiliate-withdraw-requests', AffiliateWithdrawRequestController::class)->only(['index', 'show', 'update']);
                Route::apiResource('affiliate-wallet-transactions', AffiliateWalletTransactionController::class)->only(['index', 'show']);
                Route::apiResource('vendor-withdraw-requests', VendorWithdrawRequestController::class)->only(['index', 'show', 'update']);

                Route::prefix('vendor-accounting')->group(function () {
                    Route::get('/summary', [VendorAccountingController::class, 'summary']);
                    Route::get('/vendors', [VendorAccountingController::class, 'index']);
                    Route::get('/vendors/{vendorId}', [VendorAccountingController::class, 'show']);
                });

                // Seller Registration routes
                Route::apiResource('seller-registrations', SellerRegistrationCrudController::class)->only(['index', 'show', 'destroy']);
                Route::post('seller-registrations/{id}/approve', [SellerRegistrationCrudController::class, 'approve']);
                Route::post('seller-registrations/{id}/reject', [SellerRegistrationCrudController::class, 'reject']);

                // Promotion Request routes
                Route::apiResource('promotion-requests', \App\Http\Controllers\Admin\PromotionRequest\PromotionRequestCrudController::class)->only(['index', 'show', 'destroy']);
                Route::post('promotion-requests/{id}/approve', [\App\Http\Controllers\Admin\PromotionRequest\PromotionRequestCrudController::class, 'approve']);
                Route::post('promotion-requests/{id}/reject', [\App\Http\Controllers\Admin\PromotionRequest\PromotionRequestCrudController::class, 'reject']);
                Route::get('promotion-requests/stats/summary', [\App\Http\Controllers\Admin\PromotionRequest\PromotionRequestCrudController::class, 'stats']);

                // Vendor User Management routes (includes shop assignments)
                Route::apiResource('vendor-users', VendorUserCrudController::class);
                Route::get('users/markters', [UserCrudController::class, 'markters']);
                Route::apiResource('users', UserCrudController::class);
            }

        );


        Route::prefix('orders')->group(function () {

            Route::get('/', [OrderController::class, 'index']);
            Route::get('{id}/get_one', [OrderController::class, 'get_one']);

            Route::patch('{orderId}/change-status', [OrderController::class, 'changeStatus']);

            Route::post('{orderId}/assign-driver', [OrderController::class, 'assignDriver']);

            Route::patch('items/{itemId}/change-status', [OrderController::class, 'changeItemStatus']);
        });

        Route::prefix('service-orders')->group(function () {
            Route::get('/', [ServiceOrderController::class, 'index']);
            Route::get('{id}/get_one', [ServiceOrderController::class, 'get_one']);
            Route::patch('{orderId}/change-status', [ServiceOrderController::class, 'changeStatus']);
        });
    }
);

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
