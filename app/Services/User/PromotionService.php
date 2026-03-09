<?php

namespace App\Services\User;

use App\Models\Promotion;
use Illuminate\Support\Collection;

class PromotionService
{
    /* ============================================================
     | 1️⃣ جلب العروض القابلة للاختيار (Preview/Create)
     ============================================================ */
    public function getAvailablePromotions(float $subtotal, Collection $items): Collection
    {
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
                // فقط العروض المالية القابلة للاختيار
                switch ($promotion->type) {
                    case 'simple_discount':
                        return true;
                    case 'spend_x_discount':
                        return $subtotal >= $promotion->min_spend;
                    default:
                        return false; // buy_x_get_y غير قابل للاختيار
                }
            })
            ->select('id', 'name', 'description');
    }

    /* ============================================================
     | 2️⃣ تطبيق خصم العرض المالي (Preview/Create)
     ============================================================ */
    public function applyDiscountPromotion($orderOrNull, int $promotionId, float $subtotal): float
    {
        $promotion = Promotion::find($promotionId);

        if (!$promotion || !$promotion->is_active) return 0;

        // تحقق من الحد الأدنى للإنفاق إذا كان spend_x_discount
        if ($promotion->type === 'spend_x_discount' && $subtotal < $promotion->min_spend) {
            return 0;
        }

        switch ($promotion->discount_type) {
            case 'percentage':
                return $subtotal * ($promotion->discount_value / 100);
            case 'fixed':
                return $promotion->discount_value;
            default:
                return 0;
        }
    }

    /* ============================================================
     | 3️⃣ تطبيق الهدايا (non-discount) - buy_x_get_y
     ============================================================ */
    public function applyNonDiscountPromotions($orderOrNull, Collection $orderItems): array
    {
        // جلب أحدث عرض buy_x_get_y
        $promotion = Promotion::query()
            ->where('type', 'buy_x_get_y')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest('id') // أحدث عرض
            ->first();

        if (!$promotion) {
            return [];
        }

        $appliedGifts = $this->calculateBuyXGetY($orderItems, $promotion);

        // إذا كان هناك order حقيقي أضف الهدايا
        if ($orderOrNull && !empty($appliedGifts['free_items'])) {
            foreach ($appliedGifts['free_items'] as $gift) {
                $orderOrNull->items()->create([
                    'shop_product_variant_id' => $gift['shop_product_variant_id'],
                    'product_name' => 'Gift Item',
                    'variant_attributes' => null,
                    'quantity' => $gift['free_quantity'],
                    'price' => 0,
                    'discount' => 100,
                ]);
            }
        }

        return [
            'promotion_id' => $promotion->id,
            'promotion_title' => $promotion->name,
            'free_items' => $appliedGifts['free_items'] ?? []
        ];
    }
    /* ============================================================
     | 4️⃣ منطق حساب buy_x_get_y
     ============================================================ */
    protected function calculateBuyXGetY(Collection $items, $promotion): array
    {
        $freeItems = [];

        foreach ($items as $item) {
            if ($item['quantity'] >= $promotion->buy_quantity) {
                $sets = intdiv($item['quantity'], $promotion->buy_quantity);
                $freeItems[] = [
                    'shop_product_variant_id' => $item['shop_product_variant_id'],
                    'free_quantity' => $sets * $promotion->get_quantity,
                ];
            }
        }

        return ['free_items' => $freeItems];
    }

    /* ============================================================
     | 5️⃣ تحقق شرط buy_x (Preview/Filter)
     ============================================================ */
    protected function checkBuyXCondition(Collection $items, int $buyQuantity): bool
    {
        foreach ($items as $item) {
            if ($item['quantity'] >= $buyQuantity) return true;
        }
        return false;
    }
}
