<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\VendorUser;
use App\Services\Admin\VendorAccountingService;
use App\Services\Vendor\VendorSubscriptionQuotaService;
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
        $statement = app(VendorAccountingService::class)->getVendorStatement($vendorId, [], 5);
        $wallet = (array) data_get($statement, 'wallet', []);
        $vendor = (array) data_get($statement, 'vendor', []);
        $withdrawItems = collect(data_get($statement, 'withdraw_requests.items', []));
        $latestPaidTransfer = $withdrawItems->firstWhere('status', 'paid');

        $commissionType = (string) ($wallet['commission_type'] ?? 'percentage');
        $commissionSource = (string) ($wallet['commission_source'] ?? 'package');
        $commissionSourcePackageName = (string) ($wallet['commission_source_package_name'] ?? '');

        $commissionValue = $commissionType === 'fixed'
            ? $this->formatMoney((float) ($wallet['fixed_commission'] ?? 0))
            : ((float) ($wallet['commission_rate'] ?? 0)) . '%';

        $commissionDescription = $commissionSource === 'package'
            ? __('custom.stats.commission_from_package', ['package' => $commissionSourcePackageName !== '' ? $commissionSourcePackageName : '-', 'value' => $commissionValue])
            : __('custom.stats.commission_from_package', ['package' => '-', 'value' => $commissionValue]);

        $settlementCycle = (string) ($vendor['settlement_cycle'] ?? 'monthly');
        $settlementCycleLabel = $settlementCycle === 'weekly'
            ? __('custom.stats.settlement_weekly')
            : __('custom.stats.settlement_monthly');

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
        $pendingOrders = Order::whereIn('status', ['pending'])
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

        $stats = [
            Stat::make(__('custom.stats.current_balance'), $this->formatMoney((float) ($wallet['available_for_withdraw'] ?? 0)))
                ->description(__('custom.stats.net_due_label', ['value' => $this->formatMoney((float) ($wallet['net_due'] ?? 0))]))
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),

            Stat::make(__('custom.stats.total_revenue'), $this->formatMoney((float) ($wallet['gross_sales'] ?? $totalRevenue)))
                ->description($revenueChange >= 0
                    ? '+' . number_format($revenueChange, 1) . '% ' . __('custom.stats.from_last_month')
                    : number_format($revenueChange, 1) . '% ' . __('custom.stats.from_last_month'))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            // Stat::make(__('custom.stats.platform_commission'), $this->formatMoney((float) ($wallet['platform_commission'] ?? 0)))
            //     ->description($commissionDescription)
            //     ->descriptionIcon('heroicon-m-scale')
            //     ->color('danger'),

            Stat::make(__('custom.stats.remaining_after_paid'), $this->formatMoney((float) ($wallet['remaining_after_paid'] ?? 0)))
                ->description(__('custom.stats.paid_out_label', ['value' => $this->formatMoney((float) ($wallet['paid'] ?? 0))]))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make(__('custom.stats.pending_withdrawals'), $this->formatMoney((float) ($wallet['pending_withdrawals'] ?? 0)))
                ->description(__('custom.stats.pending_requests_label', ['count' => (int) ($wallet['pending_requests_count'] ?? 0)]))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('custom.stats.paid_withdrawals'), $this->formatMoney((float) ($wallet['paid'] ?? 0)))
                ->description(
                    $latestPaidTransfer
                        ? __('custom.stats.last_transfer_reference', ['ref' => (string) ($latestPaidTransfer['transfer_reference'] ?: '-')])
                        : __('custom.stats.no_transfer_yet')
                )
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make(__('custom.stats.pending_orders_revenue'), $this->formatMoney((float) ($wallet['pending_orders_gross_sales'] ?? 0)))
                ->description(__('custom.stats.pending_orders_count_label', ['count' => (int) ($wallet['pending_orders_count'] ?? $pendingOrders)]))
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),

            Stat::make(__('custom.stats.settlement_cycle'), $settlementCycleLabel)
                ->description(__('custom.stats.next_settlement_at', ['date' => (string) ($vendor['next_settlement_at'] ?? '-')]))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

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

        $quota = app(VendorSubscriptionQuotaService::class)->getUsageSnapshot($user);

        if (!$quota['has_active']) {
            $stats[] = Stat::make(__('custom.subscription_status_title'), __('custom.subscription_status_inactive'))
                ->description(__('custom.subscription_no_active_body'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger');
        } else {
            $remainingProducts = $quota['remaining_products'];
            $remainingCampaigns = $quota['remaining_campaigns'];

            $remainingProductsLabel = $remainingProducts === null ? __('custom.unlimited') : (string) $remainingProducts;
            $remainingCampaignsLabel = $remainingCampaigns === null ? __('custom.unlimited') : (string) $remainingCampaigns;
            $daysLeftLabel = $quota['days_left'] === null ? '-' : (string) $quota['days_left'];

            $stats[] = Stat::make(__('custom.subscription_remaining_products_title'), $remainingProductsLabel)
                ->description(__('custom.subscription_remaining_products_dashboard'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info');

            $stats[] = Stat::make(__('custom.subscription_remaining_campaigns_title'), $remainingCampaignsLabel)
                ->description(__('custom.subscription_remaining_campaigns_dashboard'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary');

            $stats[] = Stat::make(__('custom.subscription_days_left_title'), $daysLeftLabel)
                ->description(__('custom.subscription_days_left_dashboard'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success');
        }

        return $stats;
    }

    private function formatMoney(float $value): string
    {
        return '$' . number_format($value, 2);
    }
}
