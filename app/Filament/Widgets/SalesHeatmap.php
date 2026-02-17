<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\VendorUser;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesHeatmap extends ChartWidget
{
    protected static ?int $sort = 6;


    public function getHeading(): string
    {
        return __('custom.stats.sales_heatmap');
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

        // أيام الأسبوع
        $days = [
            __('custom.stats.days.sunday'),
            __('custom.stats.days.monday'),
            __('custom.stats.days.tuesday'),
            __('custom.stats.days.wednesday'),
            __('custom.stats.days.thursday'),
            __('custom.stats.days.friday'),
            __('custom.stats.days.saturday'),
        ];

        // الساعات (من 8 صباحاً إلى 11 مساءً)
        $hours = [];
        for ($i = 8; $i <= 23; $i++) {
            $hours[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        }

        // جمع البيانات لآخر 30 يوم
        $heatmapData = [];

        for ($dayIndex = 0; $dayIndex < 7; $dayIndex++) {
            $dayData = [];

            for ($hour = 8; $hour <= 23; $hour++) {
                $revenue = OrderItem::whereHas('shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                    $q->where('vendor_id', $vendorId);
                })
                ->whereHas('order', function ($q) use ($dayIndex, $hour) {
                    $q->where('status', 'delivered')
                      ->where(DB::raw('DAYOFWEEK(created_at)'), $dayIndex + 1)
                      ->whereRaw('HOUR(created_at) = ?', [$hour])
                      ->where('created_at', '>=', now()->subDays(30));
                })
                ->sum(DB::raw('price * quantity'));

                $dayData[] = round($revenue, 2);
            }

            $heatmapData[] = [
                'label' => $days[$dayIndex],
                'data' => $dayData,
                'backgroundColor' => $this->getHeatmapColors($dayData),
                'borderWidth' => 1,
                'borderColor' => 'rgba(255, 255, 255, 0.5)',
            ];
        }

        return [
            'datasets' => $heatmapData,
            'labels' => $hours,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'stacked' => false,
                    'title' => [
                        'display' => true,
                        'text' => __('custom.stats.hour_of_day'),
                    ],
                ],
                'y' => [
                    'stacked' => false,
                    'title' => [
                        'display' => true,
                        'text' => __('custom.stats.day_of_week'),
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": $" + context.parsed.x.toFixed(2);
                        }',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    private function getHeatmapColors(array $data): array
    {
        $max = max($data) ?: 1;
        $colors = [];

        foreach ($data as $value) {
            $intensity = $value / $max;

            // تدرج من الأزرق الفاتح إلى الأحمر الداكن
            if ($intensity < 0.2) {
                $colors[] = 'rgba(219, 234, 254, 0.8)'; // blue-100
            } elseif ($intensity < 0.4) {
                $colors[] = 'rgba(147, 197, 253, 0.8)'; // blue-300
            } elseif ($intensity < 0.6) {
                $colors[] = 'rgba(59, 130, 246, 0.8)'; // blue-500
            } elseif ($intensity < 0.8) {
                $colors[] = 'rgba(249, 115, 22, 0.8)'; // orange-500
            } else {
                $colors[] = 'rgba(239, 68, 68, 0.8)'; // red-500
            }
        }

        return $colors;
    }
}
