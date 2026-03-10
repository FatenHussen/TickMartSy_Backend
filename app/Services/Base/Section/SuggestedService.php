<?php

namespace App\Services\Base\Section;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Basket;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\Product;

class SuggestedService
{

    public function query(array $filters = [])
    {
        $type = $filters['type'] ?? 'products';

        return match ($type) {

            'products' => $this->suggestedProducts(),

            'baskets' => $this->suggestedBaskets(),

            'shops' => $this->suggestedShops(),

            default => Product::query()
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Suggested Products
    |--------------------------------------------------------------------------
    */

    protected function suggestedProducts()
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

    /*
    |--------------------------------------------------------------------------
    | Suggested Baskets
    |--------------------------------------------------------------------------
    */

    protected function suggestedBaskets()
    {
        $userId = auth('user')->id();

        $basketIds = Order::query()
            ->where('user_id', $userId)
            ->whereNotNull('basket_id')
            ->selectRaw('basket_id, COUNT(*) as total')
            ->groupBy('basket_id')
            ->orderByDesc('total')
            ->pluck('basket_id');

        if ($basketIds->isEmpty()) {
            return Basket::query()->inRandomOrder();
        }

        return Basket::query()->whereIn('id', $basketIds);
    }

    /*
    |--------------------------------------------------------------------------
    | Suggested Shops
    |--------------------------------------------------------------------------
    */

    protected function suggestedShops()
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
            return Shop::query()->where('is_active', true)->inRandomOrder();
        }

        return Shop::query()->whereIn('id', $shopIds);
    }
}
