<?php

namespace App\Services\Base\Section;

use App\Models\OrderItem;
use App\Models\Shop;

class SuggestedShopsService
{
    public function query(array $filters = [])
    {
        $userId = auth('user')->id();

        $shopIds = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('shop_product_variants', 'shop_product_variants.id', '=', 'order_items.shop_product_variant_id')
            ->where('orders.user_id', $userId)
            ->selectRaw('shop_product_variants.shop_id, COUNT(*) as total')
            ->groupBy('shop_product_variants.shop_id')
            ->orderByDesc('total')
            ->pluck('shop_id');

        if ($shopIds->isEmpty()) {
            return Shop::query()
                ->where('is_active', true)
                ->inRandomOrder();
        }

        return Shop::query()->whereIn('id', $shopIds);
    }
}
