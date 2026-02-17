<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\VendorUser;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public ?string $filter = 'week';

    public function getHeading(): string
    {
        return __('custom.stats.sales_chart');
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => __('custom.stats.today'),
            'week' => __('custom.stats.this_week'),
            'month' => __('custom.stats.this_month'),
            'year' => __('custom.stats.this_year'),
        ];
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

        $data = match ($this->filter) {
            'today' => $this->getTodayData($vendorId),
            'week' => $this->getWeekData($vendorId),
            'month' => $this->getMonthData($vendorId),
            'year' => $this->getYearData($vendorId),
            default => $this->getWeekData($vendorId),
        };

        return [
            'datasets' => [
                [
                    'label' => __('custom.stats.sales'),
                    'data' => $data['values'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getTodayData($vendorId): array
    {
        $hours = [];
        $values = [];

        for ($i = 0; $i < 24; $i++) {
            $hours[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';

            $revenue = OrderItem::whereHas('shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function ($q) use ($i) {
                $q->where('status', 'delivered')
                  ->whereDate('created_at', today())
                  ->whereRaw('HOUR(created_at) = ?', [$i]);
            })
            ->sum(DB::raw('price * quantity'));

            $values[] = round($revenue, 2);
        }

        return ['labels' => $hours, 'values' => $values];
    }

    private function getWeekData($vendorId): array
    {
        $days = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('D');

            $revenue = OrderItem::whereHas('shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function ($q) use ($date) {
                $q->where('status', 'delivered')
                  ->whereDate('created_at', $date->format('Y-m-d'));
            })
            ->sum(DB::raw('price * quantity'));

            $values[] = round($revenue, 2);
        }

        return ['labels' => $days, 'values' => $values];
    }

    private function getMonthData($vendorId): array
    {
        $weeks = [];
        $values = [];

        for ($i = 3; $i >= 0; $i--) {
            $startDate = now()->subWeeks($i)->startOfWeek();
            $endDate = now()->subWeeks($i)->endOfWeek();

            $weeks[] = __('custom.stats.week') . ' ' . ($i + 1);

            $revenue = OrderItem::whereHas('shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'delivered')
                  ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->sum(DB::raw('price * quantity'));

            $values[] = round($revenue, 2);
        }

        return ['labels' => $weeks, 'values' => $values];
    }

    private function getYearData($vendorId): array
    {
        $months = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');

            $revenue = OrderItem::whereHas('shopProductVariant.productVariant.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function ($q) use ($date) {
                $q->where('status', 'delivered')
                  ->whereMonth('created_at', $date->month)
                  ->whereYear('created_at', $date->year);
            })
            ->sum(DB::raw('price * quantity'));

            $values[] = round($revenue, 2);
        }

        return ['labels' => $months, 'values' => $values];
    }
}
