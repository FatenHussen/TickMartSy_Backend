<?php

namespace App\Providers;

use App\Events\OrderStatusChanged;
use App\Listeners\AwardPointsListener;
use App\Models\Admin;
use App\Models\Basket;
use App\Models\Driver;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Shop;
use App\Models\Category;
use App\Models\VendorWithdrawRequest;
use App\Observers\CategoryObserver;
use App\Observers\VendorWithdrawRequestObserver;
use App\Policies\AdminPolicy;
use App\Policies\DriverPolicy;
use App\Policies\ShopPolicy;
use App\Filament\Widgets\ShopSwitcher;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
// use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'product' => Product::class,
            'shop' => Shop::class,
            'recipe' => Recipe::class,
            'basket' => Basket::class,
        ]);

        Gate::policy(Driver::class, DriverPolicy::class);
        Gate::policy(Admin::class, AdminPolicy::class);
        Gate::policy(Shop::class, ShopPolicy::class);

        Filament::registerRenderHook(
            'panels::body.end',
            fn() => view('filament.firebase-script')
        );
        // Register event listeners
        Event::listen(
            OrderStatusChanged::class,
            [AwardPointsListener::class, 'handleOrderStatusChanged']
        );

        // Vendor notifications
        Event::listen(
            OrderStatusChanged::class,
            [\App\Listeners\NotifyVendorOrderStatusChanged::class, 'handle']
        );

        Event::listen(
            \App\Events\OrderCreated::class,
            [\App\Listeners\NotifyVendorNewOrder::class, 'handle']
        );

        VendorWithdrawRequest::observe(VendorWithdrawRequestObserver::class);
        Category::observe(CategoryObserver::class);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(['ar', 'en']);
        });
    }
}
