<?php

namespace App\Services\Base;

use App\Enums\OrderStatus;
use App\Models\BasketItem;
use App\Models\Gift;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\RecipeItem;
use App\Models\ShopProductVariant;
use App\Models\UserBasketScheduleItem;

/**
 * يحسب أثر حذف متغيّر (variant) قبل تنفيذ الحذف:
 * ماذا سيُحذف فعلياً، وماذا سيتأثر فقط، وما الذي سيبقى محفوظاً.
 */
class VariantDeleteImpactService
{
    public const ACTIVE_ORDER_STATUSES = [
        OrderStatus::PENDING->value,
        OrderStatus::PREPARING->value,
        OrderStatus::OUT_DELIVERY->value,
    ];

    private const ACTIVE_ORDERS_SAMPLE_SIZE = 10;

    public function forProductVariant(ProductVariant $variant): array
    {
        $shopVariantIds = ShopProductVariant::withTrashed()
            ->where('product_variant_id', $variant->id)
            ->pluck('id')
            ->all();

        $counts = $this->countsForShopVariantIds($shopVariantIds);
        $counts['shop_variants'] = count($shopVariantIds);
        $counts['basket_items'] += BasketItem::where('variant_id', $variant->id)->count();
        $counts['images'] = $variant->media()->count();

        return $this->buildImpact('product_variant', $variant->id, $counts, $shopVariantIds);
    }

    public function forShopProductVariant(ShopProductVariant $shopVariant): array
    {
        $counts = $this->countsForShopVariantIds([$shopVariant->id]);
        $counts['shop_variants'] = 0;
        $counts['images'] = 0;

        return $this->buildImpact('shop_product_variant', $shopVariant->id, $counts, [$shopVariant->id]);
    }

    private function countsForShopVariantIds(array $shopVariantIds): array
    {
        if (empty($shopVariantIds)) {
            return [
                'active_orders' => 0,
                'past_orders' => 0,
                'basket_items' => 0,
                'recipe_items' => 0,
                'scheduled_items' => 0,
                'gifts' => 0,
            ];
        }

        $activeOrders = OrderItem::whereIn('shop_product_variant_id', $shopVariantIds)
            ->whereHas('order', fn($q) => $q->whereIn('status', self::ACTIVE_ORDER_STATUSES))
            ->distinct('order_id')
            ->count('order_id');

        $totalOrders = OrderItem::whereIn('shop_product_variant_id', $shopVariantIds)
            ->distinct('order_id')
            ->count('order_id');

        return [
            'active_orders' => $activeOrders,
            'past_orders' => max(0, $totalOrders - $activeOrders),
            'basket_items' => BasketItem::where(function ($q) use ($shopVariantIds) {
                $q->whereIn('shop_product_variant_id', $shopVariantIds);
                foreach ($shopVariantIds as $id) {
                    $q->orWhereJsonContains('shop_product_variant_ids', $id);
                }
            })->count(),
            'recipe_items' => RecipeItem::whereIn('shop_product_variant_id', $shopVariantIds)->count(),
            'scheduled_items' => UserBasketScheduleItem::whereIn('shop_product_variant_id', $shopVariantIds)->count(),
            'gifts' => Gift::whereIn('shop_product_variant_id', $shopVariantIds)->count(),
        ];
    }

    private function buildImpact(string $type, int $id, array $counts, array $shopVariantIds): array
    {
        $warnings = [];

        $addWarning = function (string $key, int $count) use (&$warnings) {
            if ($count > 0) {
                $warnings[] = [
                    'key' => $key,
                    'count' => $count,
                    'message' => __("custom.products.delete_impact.{$key}", ['count' => $count]),
                ];
            }
        };

        $addWarning('active_orders', $counts['active_orders']);
        $addWarning('past_orders', $counts['past_orders']);
        $addWarning('shop_variants', $counts['shop_variants']);
        $addWarning('basket_items', $counts['basket_items']);
        $addWarning('recipe_items', $counts['recipe_items']);
        $addWarning('scheduled_items', $counts['scheduled_items']);
        $addWarning('gifts', $counts['gifts']);

        return [
            'type' => $type,
            'id' => $id,
            'requires_confirmation' => !empty($warnings),
            'counts' => $counts,
            'warnings' => $warnings,
            'active_orders' => $this->activeOrdersSample($shopVariantIds),
        ];
    }

    private function activeOrdersSample(array $shopVariantIds): array
    {
        if (empty($shopVariantIds)) {
            return [];
        }

        return OrderItem::with('order:id,order_code,status')
            ->whereIn('shop_product_variant_id', $shopVariantIds)
            ->whereHas('order', fn($q) => $q->whereIn('status', self::ACTIVE_ORDER_STATUSES))
            ->get()
            ->pluck('order')
            ->filter()
            ->unique('id')
            ->take(self::ACTIVE_ORDERS_SAMPLE_SIZE)
            ->map(fn($order) => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'status' => $order->status,
            ])
            ->values()
            ->all();
    }
}
