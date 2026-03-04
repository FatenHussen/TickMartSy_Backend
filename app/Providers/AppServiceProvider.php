<?php

namespace App\Providers;

use App\Events\OrderStatusChanged;
use App\Listeners\AwardPointsListener;
use App\Filament\Widgets\ShopSwitcher;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
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
        // Register event listeners
        Event::listen(
            OrderStatusChanged::class,
            [AwardPointsListener::class, 'handleOrderStatusChanged']
        );
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en']); 
        });
    }
}
