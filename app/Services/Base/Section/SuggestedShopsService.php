<?php

namespace App\Services\Base\Section;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Shop;

class SuggestedShopsService
{
    public function query(array $filters = [])
    {
        $userId = auth('user')->id();
        $deliveredStatuses = [
            OrderStatus::DELIVERED->value,
            'completed',
        ];

        if ($userId) {
            $userTotals = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('shop_product_variants', 'shop_product_variants.id', '=', 'order_items.shop_product_variant_id')
                ->where('orders.user_id', $userId)
                ->whereIn('orders.status', $deliveredStatuses)
                ->selectRaw('shop_product_variants.shop_id, SUM(order_items.quantity) as total')
                ->groupBy('shop_product_variants.shop_id');

            if ((clone $userTotals)->limit(1)->exists()) {
                return Shop::query()
                    ->where('is_active', true)
                    ->select('shops.*')
                    ->joinSub($userTotals, 'user_shops', function ($join) {
                        $join->on('shops.id', '=', 'user_shops.shop_id');
                    })
                    ->orderByDesc('user_shops.total');
            }
        }

        $globalTotals = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('shop_product_variants', 'shop_product_variants.id', '=', 'order_items.shop_product_variant_id')
            ->whereIn('orders.status', $deliveredStatuses)
            ->selectRaw('shop_product_variants.shop_id, SUM(order_items.quantity) as total')
            ->groupBy('shop_product_variants.shop_id');

        if ((clone $globalTotals)->limit(1)->exists()) {
            return Shop::query()
                ->where('is_active', true)
                ->select('shops.*')
                ->joinSub($globalTotals, 'global_shops', function ($join) {
                    $join->on('shops.id', '=', 'global_shops.shop_id');
                })
                ->orderByDesc('global_shops.total');
        }

        return Shop::query()
            ->where('is_active', true)
            ->latest();
    }
}
