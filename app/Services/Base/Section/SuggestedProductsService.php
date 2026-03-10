<?php

namespace App\Services\Base\Section;

use App\Models\OrderItem;
use App\Models\Product;

class SuggestedProductsService
{
    public function query(array $filters = [])
    {
        $userId = auth('user')->id();

        $variantIds = OrderItem::query()
            ->whereHas('order', fn($q) => $q->where('user_id', $userId))
            ->pluck('shop_product_variant_id');

        if ($variantIds->isEmpty()) {
            return Product::query()->inRandomOrder();
        }

        return Product::query()
            ->whereHas('variants.shopVariants', function ($q) use ($variantIds) {
                $q->whereIn('shop_product_variants.id', $variantIds);
            })
            ->inRandomOrder();
    }
}
