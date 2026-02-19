<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\VendorUser;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class OrdersStatusChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function getHeading(): string
    {
        return __('custom.stats.orders_by_status');
    }

    protected function getData(): array
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $vendorId = $user->vendor_id;

        $pending = Order::where('status', 'pending')
            ->whereHas('items.shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->count();

        $preparing = Order::where('status', 'preparing')
            ->whereHas('items.shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->count();

        $outDelivery = Order::where('status', 'out_delivery')
            ->whereHas('items.shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->count();

        $delivered = Order::where('status', 'delivered')
            ->whereHas('items.shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->count();

        return [
            'datasets' => [
                [
                    'data' => [$pending, $preparing, $outDelivery, $delivered],
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.8)',  // warning - pending
                        'rgba(59, 130, 246, 0.8)',  // info - preparing
                        'rgba(139, 92, 246, 0.8)',  // primary - out_delivery
                        'rgba(16, 185, 129, 0.8)',  // success - delivered
                    ],
                ],
            ],
            'labels' => [
                __('custom.orders.statuses.pending'),
                __('custom.orders.statuses.preparing'),
                __('custom.orders.statuses.out_delivery'),
                __('custom.orders.statuses.delivered'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
