<?php

namespace App\Services\User;


use App\Models\Promotion;
use App\Models\ShopProductVariant;
use Illuminate\Support\Collection;
use Exception;

class PromotionService
{
    /**
     * جلب كل العروض المتاحة التي تنطبق على السلة
     */
    public function getAvailablePromotions(float $subtotal, array $orderItems): array
    {
        $promotions = Promotion::active()->get();

        $available = [];

        foreach ($promotions as $promo) {
            if ($this->isApplicable($promo, $subtotal, $orderItems)) {
                $available[] = [
                    'id' => $promo->id,
                    'name' => $promo->name,
                    'type' => $promo->type,
                    'description' => $promo->description,
                ];
            }
        }

        return $available;
    }

    /**
     * تحقق هل العرض ينطبق على السلة
     */
    public function isApplicable(Promotion $promotion, float $subtotal, array $orderItems): bool
    {
        if (!$promotion->is_active) return false;

        switch ($promotion->type) {

            case 'buy_x_get_y':
                // تحقق هل هناك كمية كافية من أي منتج
                foreach ($orderItems as $item) {
                    if ($item['quantity'] >= $promotion->min_quantity) {
                        return true;
                    }
                }
                return false;

            case 'spend_x_discount':
                return $subtotal >= $promotion->spend_amount;

            case 'spend_x_gift':
                return $subtotal >= $promotion->spend_amount;

            case 'simple_discount':
                return $subtotal > 0;

            default:
                return false;
        }
    }

    /**
     * تطبيق العرض على السلة
     * يرجع مصفوفة فيها:
     * - discount
     * - gift_items (إذا كان هناك هدايا)
     */
    public function applyPromotion(Promotion $promotion, float $subtotal, array $orderItems): array
    {
        $discount = 0;
        $giftItems = [];

        switch ($promotion->type) {

            case 'buy_x_get_y':
                // لكل منتج، حسب min_quantity نحسب free quantity
                foreach ($orderItems as $item) {
                    if ($item['quantity'] >= $promotion->min_quantity) {
                        $freeQty = intval($item['quantity'] / $promotion->min_quantity) * $promotion->free_quantity;
                        $giftItems[] = [
                            'shop_product_variant_id' => $item['shop_product_variant_id'],
                            'quantity' => $freeQty
                        ];
                    }
                }
                break;

            case 'spend_x_discount':
                if ($subtotal >= $promotion->spend_amount) {
                    $discount = $promotion->discount_amount;
                }
                break;

            case 'spend_x_gift':
                if ($subtotal >= $promotion->spend_amount) {
                    // نضيف الهدايا
                    foreach ($promotion->giftVariants as $variant) {
                        $giftItems[] = [
                            'shop_product_variant_id' => $variant->id,
                            'quantity' => 1 // أو حسب تحديد الادمن
                        ];
                    }
                }
                break;

            case 'simple_discount':
                $discount = $subtotal * ($promotion->discount_percent / 100);
                break;
        }

        return [
            'discount' => round($discount, 2),
            'gift_items' => $giftItems
        ];
    }
}
