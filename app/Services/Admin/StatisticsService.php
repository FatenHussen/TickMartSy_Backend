<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\User;
use App\Models\Driver;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * Get dashboard overview statistics
     */
    public function getDashboardOverview(?int $year = null): array
    {
        $year = $year ?? now()->year;

        return [
            'counts' => $this->getCounts(),
            'monthly_performance' => $this->getMonthlyPerformance($year),
            'orders_by_status' => $this->getOrdersByStatus(),
            'active_deliveries' => $this->getActiveDeliveries(),
            'top_shops' => $this->getTopShops(10),
            'year' => $year,
        ];
    }

    /**
     * Get counts for all entities
     */
    public function getCounts(): array
    {
        return [
            'total_users' => User::count(),
            'total_drivers' => Driver::count(),
            'active_drivers' => Driver::where('is_active', true)->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('approval_status', 'approved')->count(),
            'total_shops' => Shop::count(),
            'active_shops' => Shop::where('is_active', true)->count(),
            'total_vendors' => Vendor::count(),
            'total_orders' => Order::count(),
            'completed_orders' => Order::where('status', OrderStatus::DELIVERED->value)->count(),
        ];
    }

    /**
     * Get monthly performance (completed orders and revenue)
     */
    public function getMonthlyPerformance(int $year): array
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $data = Order::where('status', OrderStatus::DELIVERED->value)
            ->whereYear('delivered_at', $year)
            ->selectRaw('MONTH(delivered_at) as month, COUNT(*) as completed_orders, SUM(total) as total_revenue')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $result = [];
        foreach ($months as $monthNum => $monthName) {
            $monthData = $data->get($monthNum);
            $result[$monthName] = [
                'completed_orders' => $monthData->completed_orders ?? 0,
                'total_revenue' => $monthData->total_revenue ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Get orders count by status
     */
    public function getOrdersByStatus(): array
    {
        $statuses = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return [
            'pending' => $statuses[OrderStatus::PENDING->value] ?? 0,
            'preparing' => $statuses[OrderStatus::PREPARING->value] ?? 0,
            'out_for_delivery' => $statuses[OrderStatus::OUT_DELIVERY->value] ?? 0,
            'delivered' => $statuses[OrderStatus::DELIVERED->value] ?? 0,
            'cancelled' => $statuses[OrderStatus::CANCELLED->value] ?? 0,
        ];
    }

    /**
     * Get active deliveries count
     */
    public function getActiveDeliveries(): int
    {
        return Order::where('status', OrderStatus::OUT_DELIVERY->value)->count();
    }

    /**
     * Get top shops by orders count
     */
    public function getTopShops(int $limit = 10): array
    {
        return Shop::withCount(['productVariants as total_orders' => function ($query) {
            $query->join('order_items', 'shop_product_variants.id', '=', 'order_items.shop_product_variant_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', OrderStatus::DELIVERED->value);
        }])
            ->orderByDesc('total_orders')
            ->limit($limit)
            ->get()
            ->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'total_orders' => $shop->total_orders ?? 0,
                    'average_rating' => $shop->average_rating,
                    'is_active' => $shop->is_active,
                ];
            })
            ->toArray();
    }

    /**
     * Get revenue trend (daily for last 30 days)
     * Chart Type: LINE CHART
     */
    public function getRevenueTrend(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $data = Order::where('status', OrderStatus::DELIVERED->value)
            ->whereDate('delivered_at', '>=', $startDate)
            ->selectRaw('DATE(delivered_at) as date, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayData = $data->firstWhere('date', $date);

            $result[] = [
                'date' => $date,
                'day_name' => now()->subDays($i)->format('D'),
                'orders' => $dayData->orders ?? 0,
                'revenue' => $dayData->revenue ?? 0,
            ];
        }

        return [
            'chart_type' => 'line',
            'title' => 'Revenue Trend (Last ' . $days . ' Days)',
            'data' => $result,
        ];
    }

    /**
     * Get orders distribution by hour
     * Chart Type: BAR CHART (Horizontal)
     */
    public function getOrdersByHour(): array
    {
        $data = Order::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $result = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $result[] = [
                'hour' => sprintf('%02d:00', $hour),
                'count' => $data->get($hour)->count ?? 0,
            ];
        }

        return [
            'chart_type' => 'bar_horizontal',
            'title' => 'Orders Distribution by Hour',
            'data' => $result,
        ];
    }

    /**
     * Get orders distribution by day of week
     * Chart Type: BAR CHART (Vertical)
     */
    public function getOrdersByDayOfWeek(): array
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $data = Order::selectRaw('DAYOFWEEK(created_at) - 1 as day, COUNT(*) as count')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $result = [];
        foreach ($days as $index => $dayName) {
            $result[] = [
                'day' => $dayName,
                'count' => $data->get($index)->count ?? 0,
            ];
        }

        return [
            'chart_type' => 'bar_vertical',
            'title' => 'Orders Distribution by Day of Week',
            'data' => $result,
        ];
    }

    /**
     * Get revenue by payment method
     * Chart Type: PIE CHART
     */
    public function getRevenueByPaymentMethod(): array
    {
        // Assuming payment_method field exists in orders table
        $data = Order::where('status', OrderStatus::DELIVERED->value)
            ->selectRaw('payment_method, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->get();

        $result = $data->map(function ($item) {
            return [
                'label' => $item->payment_method ?? 'Cash',
                'orders' => $item->orders,
                'revenue' => $item->revenue,
                'percentage' => 0, // Will be calculated below
            ];
        })->toArray();

        // Calculate percentages
        $totalRevenue = array_sum(array_column($result, 'revenue'));
        foreach ($result as &$item) {
            $item['percentage'] = $totalRevenue > 0
                ? round(($item['revenue'] / $totalRevenue) * 100, 2)
                : 0;
        }

        return [
            'chart_type' => 'pie',
            'title' => 'Revenue by Payment Method',
            'data' => $result,
        ];
    }

    /**
     * Get top categories by revenue
     * Chart Type: DOUGHNUT CHART
     */
    public function getTopCategoriesByRevenue(int $limit = 8): array
    {
        $data = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('shop_product_variants', 'order_items.shop_product_variant_id', '=', 'shop_product_variants.id')
            ->join('product_variants', 'shop_product_variants.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', OrderStatus::DELIVERED->value)
            ->selectRaw('categories.id, categories.name, SUM(order_items.price * order_items.quantity) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        $result = $data->map(function ($item) {
            return [
                'category_id' => $item->id,
                'category_name' => json_decode($item->name, true),
                'revenue' => $item->revenue,
            ];
        })->toArray();

        return [
            'chart_type' => 'doughnut',
            'title' => 'Top Categories by Revenue',
            'data' => $result,
        ];
    }

    /**
     * Get user growth over time
     * Chart Type: AREA CHART
     */
    public function getUserGrowth(int $months = 12): array
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        $data = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as new_users')
            ->where('created_at', '>=', $startDate)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $result = [];
        $cumulativeUsers = User::where('created_at', '<', $startDate)->count();

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthName = now()->subMonths($i)->format('M Y');
            $newUsers = $data->get($month)->new_users ?? 0;
            $cumulativeUsers += $newUsers;

            $result[] = [
                'month' => $monthName,
                'new_users' => $newUsers,
                'total_users' => $cumulativeUsers,
            ];
        }

        return [
            'chart_type' => 'area',
            'title' => 'User Growth (Last ' . $months . ' Months)',
            'data' => $result,
        ];
    }

    /**
     * Get order status funnel
     * Chart Type: FUNNEL CHART
     */
    public function getOrderStatusFunnel(): array
    {
        $statuses = [
            ['status' => 'pending', 'label' => 'Pending'],
            ['status' => 'preparing', 'label' => 'Preparing'],
            ['status' => 'out_delivery', 'label' => 'Out for Delivery'],
            ['status' => 'delivered', 'label' => 'Delivered'],
        ];

        $result = [];
        foreach ($statuses as $status) {
            $count = Order::where('status', $status['status'])->count();
            $result[] = [
                'stage' => $status['label'],
                'count' => $count,
            ];
        }

        return [
            'chart_type' => 'funnel',
            'title' => 'Order Status Funnel',
            'data' => $result,
        ];
    }

    /**
     * Get average order value trend
     * Chart Type: LINE CHART (Smooth)
     */
    public function getAverageOrderValueTrend(int $months = 6): array
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        $data = Order::where('status', OrderStatus::DELIVERED->value)
            ->where('delivered_at', '>=', $startDate)
            ->selectRaw('DATE_FORMAT(delivered_at, "%Y-%m") as month, AVG(total) as avg_value')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthName = now()->subMonths($i)->format('M Y');

            $result[] = [
                'month' => $monthName,
                'average_order_value' => round($data->get($month)->avg_value ?? 0, 2),
            ];
        }

        return [
            'chart_type' => 'line_smooth',
            'title' => 'Average Order Value Trend',
            'data' => $result,
        ];
    }

    /**
     * Get driver performance comparison
     * Chart Type: RADAR CHART
     */
    public function getDriverPerformanceComparison(int $limit = 5): array
    {
        $drivers = Driver::where('is_active', true)
            ->withCount(['completedOrders'])
            ->with(['ratings'])
            ->orderByDesc('completed_orders_count')
            ->limit($limit)
            ->get();

        $result = $drivers->map(function ($driver) {
            $avgDeliveryTime = $driver->completedOrders()
                ->whereNotNull('out_delivery_at')
                ->whereNotNull('delivered_at')
                ->get()
                ->map(function ($order) {
                    return \Carbon\Carbon::parse($order->out_delivery_at)
                        ->diffInMinutes(\Carbon\Carbon::parse($order->delivered_at));
                })
                ->avg();

            return [
                'driver_name' => $driver->name,
                'total_orders' => $driver->completed_orders_count,
                'average_rating' => round($driver->ratings->avg('rating') ?? 0, 1),
                'avg_delivery_time' => round($avgDeliveryTime ?? 0, 1),
            ];
        })->toArray();

        return [
            'chart_type' => 'radar',
            'title' => 'Top Drivers Performance Comparison',
            'data' => $result,
        ];
    }

    /**
     * Get product stock levels
     * Chart Type: GAUGE CHART (Multiple)
     */
    public function getProductStockLevels(): array
    {
        $totalProducts = Product::where('approval_status', 'approved')->count();
        $lowStock = Product::where('approval_status', 'approved')
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 10)
            ->count();
        $outOfStock = Product::where('approval_status', 'approved')
            ->where('quantity', 0)
            ->count();
        $inStock = $totalProducts - $lowStock - $outOfStock;

        return [
            'chart_type' => 'gauge',
            'title' => 'Product Stock Levels',
            'data' => [
                [
                    'label' => 'In Stock',
                    'value' => $inStock,
                    'percentage' => $totalProducts > 0 ? round(($inStock / $totalProducts) * 100, 1) : 0,
                    'color' => 'green',
                ],
                [
                    'label' => 'Low Stock',
                    'value' => $lowStock,
                    'percentage' => $totalProducts > 0 ? round(($lowStock / $totalProducts) * 100, 1) : 0,
                    'color' => 'orange',
                ],
                [
                    'label' => 'Out of Stock',
                    'value' => $outOfStock,
                    'percentage' => $totalProducts > 0 ? round(($outOfStock / $totalProducts) * 100, 1) : 0,
                    'color' => 'red',
                ],
            ],
        ];
    }

    /**
     * Get sales heatmap (day of week vs hour)
     * Chart Type: HEATMAP
     */
    public function getSalesHeatmap(): array
    {
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        $data = Order::selectRaw('
                DAYOFWEEK(created_at) - 1 as day,
                HOUR(created_at) as hour,
                COUNT(*) as count
            ')
            ->groupBy('day', 'hour')
            ->get();

        $heatmapData = [];
        foreach ($days as $dayIndex => $dayName) {
            for ($hour = 0; $hour < 24; $hour++) {
                $count = $data->where('day', $dayIndex)
                    ->where('hour', $hour)
                    ->first()->count ?? 0;

                $heatmapData[] = [
                    'day' => $dayName,
                    'hour' => sprintf('%02d:00', $hour),
                    'value' => $count,
                ];
            }
        }

        return [
            'chart_type' => 'heatmap',
            'title' => 'Sales Heatmap (Day vs Hour)',
            'data' => $heatmapData,
        ];
    }
}
