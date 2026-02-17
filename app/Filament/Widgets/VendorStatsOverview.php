<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\VendorUser;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return [];
        }

        $vendorId = $user->vendor_id;

        // إجمالي المبيعات
        $totalRevenue = OrderItem::whereHas('shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })
        ->whereHas('order', function ($q) {
            $q->where('status', 'delivered');
        })
        ->sum(DB::raw('price * quantity'));

        // عدد الطلبات المكتملة
        $completedOrders = Order::where('status', 'delivered')
            ->whereHas('items.shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->count();

        // عدد المنتجات
        $totalProducts = Product::where('vendor_id', $vendorId)->count();

        // الطلبات قيد التحضير
        $pendingOrders = Order::whereIn('status', ['pending', 'preparing'])
            ->whereHas('items.shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->count();

        // مبيعات هذا الشهر
        $thisMonthRevenue = OrderItem::whereHas('shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })
        ->whereHas('order', function ($q) {
            $q->where('status', 'delivered')
              ->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
        })
        ->sum(DB::raw('price * quantity'));

        // مبيعات الشهر الماضي
        $lastMonthRevenue = OrderItem::whereHas('shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })
        ->whereHas('order', function ($q) {
            $q->where('status', 'delivered')
              ->whereMonth('created_at', now()->subMonth()->month)
              ->whereYear('created_at', now()->subMonth()->year);
        })
        ->sum(DB::raw('price * quantity'));

        $revenueChange = $lastMonthRevenue > 0
            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        return [
            Stat::make(__('custom.stats.total_revenue'), '$' . number_format($totalRevenue, 2))
                ->description($revenueChange >= 0
                    ? '+' . number_format($revenueChange, 1) . '% ' . __('custom.stats.from_last_month')
                    : number_format($revenueChange, 1) . '% ' . __('custom.stats.from_last_month'))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make(__('custom.stats.completed_orders'), $completedOrders)
                ->description(__('custom.stats.total_completed'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('custom.stats.total_products'), $totalProducts)
                ->description(__('custom.stats.in_catalog'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make(__('custom.stats.pending_orders'), $pendingOrders)
                ->description(__('custom.stats.need_attention'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
