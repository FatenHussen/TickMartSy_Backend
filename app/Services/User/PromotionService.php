<?php

namespace App\Services\User;

use App\Models\Promotion;
use App\Models\ShopProductVariant;
use App\Services\PointService;
use Illuminate\Support\Collection;

class PromotionService
{
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
            ->filter(function ($promotion) use ($subtotal) {
                switch ($promotion->type) {
                    case 'simple_discount':
                        return true;
                    case 'spend_x_discount':
                    case 'spend_x_get_gift':
                    case 'spend_x_get_points':
                    case 'free_shipping':
                        return $subtotal >= ($promotion->min_spend ?? 0);
                    default:
                        return false;
                }
            })
            ->select('id', 'name', 'description');
    }

    public function applyDiscountPromotion($orderOrNull, int $promotionId, float $subtotal): float
    {
        $promotion = Promotion::find($promotionId);

        if (!$promotion || !$promotion->is_active) {
            return 0;
        }

        $needsMinSpend = in_array(
            $promotion->type,
            ['spend_x_discount', 'spend_x_get_gift', 'spend_x_get_points', 'free_shipping'],
            true
        );

        if ($promotion->type === 'spend_x_get_points') {
            $this->awardSpendXGetPoints($orderOrNull, $promotion);
            return 0;
        }

        if ($needsMinSpend && $subtotal < ($promotion->min_spend ?? 0)) {
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

    public function applyNonDiscountPromotions(
        $orderOrNull,
        Collection $orderItems,
        ?int $promotionId = null,
        float $subtotal = 0,
        ?int $userId = null,
        bool $isPreview = true
    ): array {
        $promotion = null;

        if ($promotionId) {
            $promotion = Promotion::find($promotionId);

            if (
                !$promotion ||
                !$this->isPromotionActive($promotion) ||
                ($promotion->min_spend !== null && $subtotal < $promotion->min_spend)
            ) {
                $promotion = null;
            }
        }

        if ($promotion) {
            return $this->applyPromotionByType(
                $orderOrNull,
                $orderItems,
                $promotion,
                $subtotal,
                $userId,
                $isPreview
            );
        }

        return $this->applyBuyXGetY($orderOrNull, $orderItems, $isPreview);
    }

    protected function applyPromotionByType(
        $orderOrNull,
        Collection $orderItems,
        Promotion $promotion,
        float $subtotal,
        ?int $userId,
        bool $isPreview
    ): array {
        switch ($promotion->type) {
            case 'buy_x_get_y':
                return $this->applyBuyXGetY($orderOrNull, $orderItems, $isPreview, $promotion);
            case 'spend_x_get_gift':
                return array_merge(
                    $this->buildPromotionMeta($promotion),
                    $this->handleSpendXGetGift($orderOrNull, $promotion, $isPreview)
                );
            case 'spend_x_get_points':
                return array_merge(
                    $this->buildPromotionMeta($promotion),
                    $this->handleSpendXGetPoints($orderOrNull, $promotion, $userId, $isPreview)
                );
            case 'free_shipping':
                return array_merge(
                    $this->buildPromotionMeta($promotion),
                    ['free_shipping' => true]
                );
            default:
                return [];
        }
    }

    protected function applyBuyXGetY(
        $orderOrNull,
        Collection $orderItems,
        bool $isPreview,
        ?Promotion $specificPromotion = null
    ): array {
        $promotion = $specificPromotion;

        if (!$promotion) {
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
                ->latest('id')
                ->first();
        }

        if (!$promotion) {
            return [];
        }

        $appliedGifts = $this->calculateBuyXGetY($orderItems, $promotion);

        if ($orderOrNull && !$isPreview && !empty($appliedGifts['free_items'])) {
            $this->attachGiftItems($orderOrNull, $appliedGifts['free_items']);
        }

        return array_merge($this->buildPromotionMeta($promotion), [
            'free_items' => $appliedGifts['free_items'] ?? [],
        ]);
    }

    protected function handleSpendXGetGift(
        $orderOrNull,
        Promotion $promotion,
        bool $isPreview
    ): array {
        $giftIds = collect($promotion->gift_product_ids ?? [])->filter()->values();
        $freeItems = [];

        foreach ($giftIds as $variantId) {
            $freeItems[] = [
                'shop_product_variant_id' => $variantId,
                'free_quantity' => 1,
            ];
        }

        if ($orderOrNull && !$isPreview && !empty($freeItems)) {
            $this->attachGiftItems($orderOrNull, $freeItems);
        }

        return ['free_items' => $freeItems];
    }

    protected function handleSpendXGetPoints(
        $orderOrNull,
        Promotion $promotion,
        ?int $userId,
        bool $isPreview
    ): array {
        $points = (int) ($promotion->reward_points ?? 0);

        if ($points <= 0) {
            return [];
        }

        if ($isPreview) {
            return ['points_expected' => $points];
        }

        if (!$userId) {
            return [];
        }

        $pointService = app(PointService::class);

        $transaction = $pointService->addPointsToWallet(
            $userId,
            $points,
            null,
            'promotion',
            'earned',
            'order',
            $orderOrNull?->id,
            null,
            null,
            "Spend X Get Points promotion #{$promotion->id}"
        );

        if ($transaction) {
            return ['points_awarded' => abs($transaction->points)];
        }

        return [];
    }

    protected function attachGiftItems($order, array $freeItems): void
    {
        foreach ($freeItems as $gift) {
            $shopVariant = ShopProductVariant::with('productVariant.product')
                ->find($gift['shop_product_variant_id']);

            if (!$shopVariant) {
                continue;
            }

            $order->items()->create([
                'shop_product_variant_id' => $shopVariant->id,
                'product_name' => $shopVariant->productVariant->product->name . ' Gift',
                'variant_attributes' => $shopVariant->productVariant->getAttributesValuesAttribute(),
                'quantity' => $gift['free_quantity'],
                'price' => 0,
                'unit_price' => 0,
                'final_price' => 0,
                'subtotal' => 0,
                'extras_total' => 0,
                'total' => 0,
            ]);
        }
    }

    protected function buildPromotionMeta(Promotion $promotion): array
    {
        return [
            'promotion_id' => $promotion->id,
            'promotion_title' => $promotion->name,
            'promotion_type' => $promotion->type,
        ];
    }

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

    protected function isPromotionActive(Promotion $promotion): bool
    {
        if (!$promotion->is_active) {
            return false;
        }

        if ($promotion->starts_at && $promotion->starts_at->isFuture()) {
            return false;
        }

        if ($promotion->ends_at && $promotion->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    protected function awardSpendXGetPoints($orderOrNull, Promotion $promotion): void
    {
        $points = (int) ($promotion->reward_points ?? 0);

        if (!$orderOrNull || $points <= 0 || !$orderOrNull->user_id) {
            return;
        }

        $pointService = app(PointService::class);

        $pointService->addPointsToWallet(
            $orderOrNull->user_id,
            $points,
            null,
            'promotion',
            'earned',
            'order',
            $orderOrNull->id,
            null,
            null,
            "Spend X Get Points promotion #{$promotion->id}"
        );
    }
}
