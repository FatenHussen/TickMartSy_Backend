<?php

namespace App\Services\User;

use App\Models\Promotion;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PromotionService2
{
    /* ============================================================
     | 1️⃣ جلب العروض المتاحة حسب السلة
     ============================================================ */
    public function getAvailablePromotions(
        float $subtotal,
        Collection $items
    ): Collection {

        return Promotion::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->get()
            ->filter(function ($promotion) use ($subtotal, $items) {

                switch ($promotion->type) {

                    case 'spend_x_discount':
                    case 'spend_x_gift':
                        // return $subtotal >= $promotion->min_spend;
                        return false;

                    case 'simple_discount':
                        return true;

                    case 'buy_x_get_y':
                        return $this->checkBuyXCondition(
                            $items,
                            $promotion->buy_quantity
                        );

                    default:
                        return false;
                }
            })
            ->values();
    }

    /* ============================================================
     | 2️⃣ حساب خصم عرض مالي (preview + create)
     ============================================================ */
    public function calculateDiscount(
        int $promotionId,
        float $subtotal
    ): float {

        $promotion = Promotion::find($promotionId);

        if (!$promotion || !$promotion->is_active) {
            return 0;
        }

        switch ($promotion->type) {

            case 'simple_discount':
                if ($promotion->discount_percent) {
                    return $subtotal *
                        ($promotion->discount_percent / 100);
                }

                return $promotion->discount_amount ?? 0;

            case 'spend_x_discount':
                if ($subtotal < $promotion->min_spend) {
                    return 0;
                }

                if ($promotion->discount_percent) {
                    return $subtotal *
                        ($promotion->discount_percent / 100);
                }

                return $promotion->discount_amount ?? 0;

            default:
                return 0;
        }
    }

    /* ============================================================
     | 3️⃣ Preview عروض الهدايا
     ============================================================ */
    public function previewNonDiscountPromotions(
        int $promotionId,
        Collection $items,
        float $subtotal
    ): array {

        $promotion = Promotion::find($promotionId);

        if (!$promotion || !$promotion->is_active) {
            return [];
        }

        switch ($promotion->type) {

            case 'buy_x_get_y':
                return $this->calculateBuyXGetY(
                    $items,
                    $promotion
                );

                // case 'spend_x_gift':
                //     if ($subtotal >= $promotion->min_spend) {
                //         return [
                //             'gifts' => $promotion
                //                 ->giftProducts()
                //                 ->pluck('id')
                //         ];
                //     }
                //     return [];

            default:
                return [];
        }
    }

    /* ============================================================
     | 4️⃣ منطق buy_x_get_y
     ============================================================ */
    protected function calculateBuyXGetY(
        Collection $items,
        $promotion
    ): array {

        $freeItems = [];

        foreach ($items as $item) {

            if ($item['quantity'] >= $promotion->buy_quantity) {

                $sets = intdiv(
                    $item['quantity'],
                    $promotion->buy_quantity
                );

                $freeItems[] = [
                    'shop_product_variant_id' =>
                    $item['shop_product_variant_id'],
                    'free_quantity' =>
                    $sets * $promotion->get_quantity
                ];
            }
        }

        return [
            'free_items' => $freeItems
        ];
    }

    /* ============================================================
     | 5️⃣ تحقق شرط buy_x
     ============================================================ */
    protected function checkBuyXCondition(
        Collection $items,
        int $buyQuantity
    ): bool {

        foreach ($items as $item) {
            if ($item['quantity'] >= $buyQuantity) {
                return true;
            }
        }

        return false;
    }
}
