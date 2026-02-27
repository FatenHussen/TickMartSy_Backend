<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use App\Models\Driver;
use App\Models\Rating;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsService
{
    /**
     * Get sales report with filters
     */
    public function getSalesReport(array $filters = []): array
    {
        $query = Order::where('status', OrderStatus::DELIVERED->value);

        // Apply date filters
        if (!empty($filters['from_date'])) {
            $query->whereDate('delivered_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('delivered_at', '<=', $filters['to_date']);
        }

        // Apply location filters
        if (!empty($filters['governorate_id'])) {
            $query->whereHas('address', function ($q) use ($filters) {
                $q->whereHas('area', function ($sq) use ($filters) {
                    $sq->whereHas('city', function ($tq) use ($filters) {
                        $tq->where('governorate_id', $filters['governorate_id']);
                    });
                });
            });
        }

        if (!empty($filters['city_id'])) {
            $query->whereHas('address', function ($q) use ($filters) {
                $q->whereHas('area', function ($sq) use ($filters) {
                    $sq->where('city_id', $filters['city_id']);
                });
            });
        }

        // Apply vendor/shop filters
        if (!empty($filters['vendor_id'])) {
            $query->whereHas('items.shopProductVariant.shop', function ($q) use ($filters) {
                $q->where('vendor_id', $filters['vendor_id']);
            });
        }

        if (!empty($filters['shop_id'])) {
            $query->whereHas('items.shopProductVariant', function ($q) use ($filters) {
                $q->where('shop_id', $filters['shop_id']);
            });
        }

        // Apply payment method filter
        if (!empty($filters['payment_method'])) {
            // Assuming payment_method is stored in orders table
            // $query->where('payment_method', $filters['payment_method']);
        }

        $orders = $query->with(['items', 'user', 'driver'])->get();

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total'),
            'total_delivery_fees' => $orders->sum('delivery_price'),
            'total_discounts' => $orders->sum('coupon_discount') + $orders->sum('basket_discount'),
            'average_order_value' => $orders->avg('total'),
            'orders' => $orders->map(function ($order) {
                return [
                    'order_code' => $order->order_code,
                    'user' => $order->user->name ?? 'N/A',
                    'total' => $order->total,
                    'delivery_price' => $order->delivery_price,
                    'delivered_at' => $order->delivered_at,
                ];
            }),
        ];
    }

    /**
     * Get product movement report
     */
    public function getProductMovementReport(array $filters = []): array
    {
        $query = OrderItem::whereHas('order', function ($q) {
            $q->where('status', OrderStatus::DELIVERED->value);
        });

        // Apply date filters
        if (!empty($filters['from_date'])) {
            $query->whereHas('order', function ($q) use ($filters) {
                $q->whereDate('delivered_at', '>=', $filters['from_date']);
            });
        }
        if (!empty($filters['to_date'])) {
            $query->whereHas('order', function ($q) use ($filters) {
                $q->whereDate('delivered_at', '<=', $filters['to_date']);
            });
        }

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->whereHas('shopProductVariant.productVariant.product', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        // Get top selling products
        $topSelling = (clone $query)
            ->select('shop_product_variant_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->groupBy('shop_product_variant_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->with('shopProductVariant.productVariant.product')
            ->get();

        // Get least selling products
        $leastSelling = (clone $query)
            ->select('shop_product_variant_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->groupBy('shop_product_variant_id')
            ->orderBy('total_sold')
            ->limit(10)
            ->with('shopProductVariant.productVariant.product')
            ->get();

        // Get inactive products (no sales)
        $inactiveProducts = Product::whereDoesntHave('variants.shopVariants.orderItems', function ($q) use ($filters) {
            $q->whereHas('order', function ($sq) use ($filters) {
                $sq->where('status', OrderStatus::DELIVERED->value);
                if (!empty($filters['from_date'])) {
                    $sq->whereDate('delivered_at', '>=', $filters['from_date']);
                }
                if (!empty($filters['to_date'])) {
                    $sq->whereDate('delivered_at', '<=', $filters['to_date']);
                }
            });
        })
            ->where('approval_status', 'approved')
            ->limit(20)
            ->get();

        return [
            'top_selling' => $topSelling->map(function ($item) {
                $product = $item->shopProductVariant->productVariant->product ?? null;
                return [
                    'product_id' => $product->id ?? null,
                    'product_name' => $product->name ?? 'N/A',
                    'total_sold' => $item->total_sold,
                    'total_revenue' => $item->total_revenue,
                ];
            }),
            'least_selling' => $leastSelling->map(function ($item) {
                $product = $item->shopProductVariant->productVariant->product ?? null;
                return [
                    'product_id' => $product->id ?? null,
                    'product_name' => $product->name ?? 'N/A',
                    'total_sold' => $item->total_sold,
                    'total_revenue' => $item->total_revenue,
                ];
            }),
            'inactive_products' => $inactiveProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name ?? 'N/A',
                ];
            }),
        ];
    }

    /**
     * Get vendor performance report
     */
    public function getVendorPerformanceReport(int $vendorId, array $filters = []): array
    {
        $vendor = Vendor::findOrFail($vendorId);

        $query = Order::where('status', OrderStatus::DELIVERED->value)
            ->whereHas('items.shopProductVariant.shop', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            });

        // Apply date filters
        if (!empty($filters['from_date'])) {
            $query->whereDate('delivered_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('delivered_at', '<=', $filters['to_date']);
        }

        $orders = $query->get();

        // Get ratings for vendor's shops
        $shopIds = $vendor->shops->pluck('id');
        $ratings = Rating::where('rateable_type', Shop::class)
            ->whereIn('rateable_id', $shopIds)
            ->get();

        return [
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->name,
            'total_sales' => $orders->sum('total'),
            'total_orders' => $orders->count(),
            'average_order_value' => $orders->avg('total'),
            'total_shops' => $vendor->shops->count(),
            'active_shops' => $vendor->shops->where('is_active', true)->count(),
            'average_rating' => $ratings->avg('rating') ?? 0,
            'total_ratings' => $ratings->count(),
            'customer_satisfaction' => $this->calculateSatisfactionRate($ratings),
        ];
    }

    /**
     * Get driver performance report
     */
    public function getDriverPerformanceReport(int $driverId, array $filters = []): array
    {
        $driver = Driver::findOrFail($driverId);

        $query = Order::where('driver_id', $driverId)
            ->where('status', OrderStatus::DELIVERED->value);

        // Apply date filters
        if (!empty($filters['from_date'])) {
            $query->whereDate('delivered_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('delivered_at', '<=', $filters['to_date']);
        }

        $orders = $query->get();

        // Calculate average delivery time
        $avgDeliveryTime = $orders->map(function ($order) {
            if ($order->out_delivery_at && $order->delivered_at) {
                return Carbon::parse($order->out_delivery_at)
                    ->diffInMinutes(Carbon::parse($order->delivered_at));
            }
            return null;
        })->filter()->avg();

        // Get driver ratings
        $ratings = Rating::where('rateable_type', Driver::class)
            ->where('rateable_id', $driverId)
            ->get();

        // Get complaints (if exists)
        $complaints = DB::table('complaints')
            ->where('driver_id', $driverId)
            ->count();

        return [
            'driver_id' => $driver->id,
            'driver_name' => $driver->name,
            'total_orders' => $orders->count(),
            'total_earnings' => $orders->sum('delivery_price'),
            'average_delivery_time_minutes' => round($avgDeliveryTime ?? 0, 2),
            'average_rating' => $ratings->avg('rating') ?? 0,
            'total_ratings' => $ratings->count(),
            'total_complaints' => $complaints,
        ];
    }

    /**
     * Get sales by location report
     */
    public function getSalesByLocationReport(array $filters = []): array
    {
        $query = Order::where('status', OrderStatus::DELIVERED->value)
            ->with(['address.area.city.governorate']);

        // Apply date filters
        if (!empty($filters['from_date'])) {
            $query->whereDate('delivered_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('delivered_at', '<=', $filters['to_date']);
        }

        $orders = $query->get();

        // Group by governorate
        $byGovernorate = $orders->groupBy(function ($order) {
            return $order->address->area->city->governorate->name ?? 'Unknown';
        })->map(function ($orders, $governorate) {
            return [
                'governorate' => $governorate,
                'total_orders' => $orders->count(),
                'total_revenue' => $orders->sum('total'),
            ];
        })->values();

        // Group by city
        $byCity = $orders->groupBy(function ($order) {
            return $order->address->area->city->name ?? 'Unknown';
        })->map(function ($orders, $city) {
            return [
                'city' => $city,
                'total_orders' => $orders->count(),
                'total_revenue' => $orders->sum('total'),
            ];
        })->values();

        return [
            'by_governorate' => $byGovernorate,
            'by_city' => $byCity,
        ];
    }

    /**
     * Get sales by category report
     */
    public function getSalesByCategoryReport(array $filters = []): array
    {
        $query = OrderItem::whereHas('order', function ($q) use ($filters) {
            $q->where('status', OrderStatus::DELIVERED->value);
            if (!empty($filters['from_date'])) {
                $q->whereDate('delivered_at', '>=', $filters['from_date']);
            }
            if (!empty($filters['to_date'])) {
                $q->whereDate('delivered_at', '<=', $filters['to_date']);
            }
        })->with('shopProductVariant.productVariant.product.category');

        $items = $query->get();

        $byCategory = $items->groupBy(function ($item) {
            return $item->shopProductVariant->productVariant->product->category->name ?? 'Unknown';
        })->map(function ($items, $category) {
            return [
                'category' => $category,
                'total_quantity' => $items->sum('quantity'),
                'total_revenue' => $items->sum(function ($item) {
                    return $item->price * $item->quantity;
                }),
            ];
        })->values();

        return [
            'by_category' => $byCategory,
        ];
    }

    /**
     * Calculate customer satisfaction rate
     */
    private function calculateSatisfactionRate($ratings): float
    {
        if ($ratings->isEmpty()) {
            return 0;
        }

        $satisfiedCount = $ratings->where('rating', '>=', 4)->count();
        return round(($satisfiedCount / $ratings->count()) * 100, 2);
    }
}
