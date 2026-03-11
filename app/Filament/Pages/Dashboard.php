<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            // \App\Filament\Widgets\VendorNotificationsWidget::class,
            \App\Filament\Widgets\VendorStatsOverview::class,
            \App\Filament\Widgets\SalesChart::class,
            \App\Filament\Widgets\SalesHeatmap::class,
            \App\Filament\Widgets\TopProductsChart::class,
            \App\Filament\Widgets\OrdersStatusChart::class,
            \App\Filament\Widgets\LowStockProducts::class,
        ];
    }
}
