<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\VendorUser;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TopProductsChart extends ChartWidget
{
    protected static ?int $sort = 3;

    // protected int | string | array $columnSpan = [
    //     'md' => 2,
    //     'xl' => 2,
    // ];

    public function getHeading(): string
    {
        return __('custom.stats.top_products');
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

        // أعلى 10 منتجات مبيعاً
        $topProducts = Product::where('vendor_id', $vendorId)
            ->withCount(['orderItems as total_sold' => function ($query) {
                $query->select(DB::raw('SUM(order_items.quantity)'))
                    ->whereHas('order', function ($q) {
                        $q->where('status', 'delivered');
                    });
            }])
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        $labels = $topProducts->map(fn($p) => $p->name)->toArray();
        $values = $topProducts->map(fn($p) => $p->total_sold ?? 0)->toArray();

        return [
            'datasets' => [
                [
                    'label' => __('custom.stats.units_sold'),
                    'data' => $values,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
