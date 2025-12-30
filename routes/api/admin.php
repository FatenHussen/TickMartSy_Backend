<?php

use App\Http\Controllers\Admin\Admin\AdminCrudController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Role_Permission\PermissionIndexController;
use App\Http\Controllers\Admin\Role_Permission\RoleCrudController;
use App\Http\Controllers\Admin\Store\StoreCrudController;
use App\Http\Controllers\Admin\Shop\ShopCrudController;
use App\Http\Controllers\Admin\Vendor\VendorCrudController;
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



        // Route::middleware('auth:admin')->group(function () {
        //     Route::apiResources([
        //         'stores'       => StoreCrudController::class,
        //         'shops'       => ShopCrudController::class,
        //         'vendors'       => VendorCrudController::class,
        //    ]);

        Route::apiResource('shops', ShopCrudController::class)
            ->middleware('crud.permission:shops');

        Route::apiResource('stores', StoreCrudController::class)
            ->middleware('crud.permission:stores');

        Route::apiResource('vendors', VendorCrudController::class);

        Route::apiResource('roles', RoleCrudController::class);
        Route::get('permissions', [PermissionIndexController::class, 'index']);


        Route::apiResource('admins', AdminCrudController::class);
        // });
    }
);
