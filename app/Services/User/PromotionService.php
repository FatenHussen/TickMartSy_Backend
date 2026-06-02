<?php

namespace App\Services\User;

use App\Models\Product;
use App\Models\Promotion;
use App\Services\PointService;
use Illuminate\Support\Collection;

class PromotionService
{
    public const USER_SELECTABLE_TYPES = ['simple_discount', 'spend_x_discount'];

    public const AUTOMATIC_TYPES = [
        'spend_x_get_gift',
        'spend_x_get_points',
        'free_shipping',
        'spend_x_get_free_shipping',
    ];

    public function getAvailablePromotions(float $subtotal, Collection $items): Collection
    {
        $normalizedItems = $this->normalizeOrderItems($items);

        return Promotion::query()
            ->whereIn('type', self::USER_SELECTABLE_TYPES)
            ->where('is_active', true)
            ->with(['products:id', 'categories:id', 'shops:id', 'vendors:id'])
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('id')
            ->get()
            ->filter(function (Promotion $promotion) use ($subtotal, $normalizedItems) {
                $eligibleSubtotal = $this->resolveEligibleSubtotal($promotion, $subtotal, $normalizedItems);

                if ($promotion->type === 'simple_discount') {
                    return $eligibleSubtotal > 0;
                }

                if ($promotion->type === 'spend_x_discount') {
                    return $eligibleSubtotal >= (float) ($promotion->min_spend ?? 0);
                }

                return false;
            })
            ->values()
            ->map(fn (Promotion $promotion) => [
                'id' => $promotion->id,
                'name' => $promotion->name,
                'description' => $promotion->description,
            ]);
    }

    public function applyDiscountPromotion($orderOrNull, int $promotionId, float $subtotal, ?Collection $items = null): float
    {
        $details = $this->evaluateDiscountPromotion(
            $promotionId,
            $subtotal,
            $items ?? collect()
        );

        return (float) ($details['discount'] ?? 0);
    }

    public function evaluateDiscountPromotion(int $promotionId, float $subtotal, Collection $items): array
    {
        $promotion = Promotion::query()
            ->with(['products:id', 'categories:id', 'shops:id', 'vendors:id'])
            ->find($promotionId);

        if (
            ! $promotion
            || ! $this->isPromotionActive($promotion)
            || ! in_array($promotion->type, self::USER_SELECTABLE_TYPES, true)
        ) {
            return $this->emptyDiscountPromotionResult($promotionId);
        }

        $metrics = $this->computeEligibilityMetrics($this->normalizeOrderItems($items), $promotion);
        $eligibleSubtotal = $metrics['eligible_subtotal'];

        if ($eligibleSubtotal <= 0) {
            return $this->emptyDiscountPromotionResult($promotion->id, $promotion->type, $metrics);
        }

        if (
            $promotion->type === 'spend_x_discount'
            && $eligibleSubtotal < (float) ($promotion->min_spend ?? 0)
        ) {
            return $this->emptyDiscountPromotionResult($promotion->id, $promotion->type, $metrics);
        }

        $discount = $this->calculateDiscountAmount($promotion, $eligibleSubtotal);

        return [
            'id' => $promotion->id,
            'type' => $promotion->type,
            'eligible_subtotal' => round($eligibleSubtotal, 2),
            'discount' => round($discount, 2),
            'eligible_products' => $metrics['eligible_products'],
            'ineligible_products' => $metrics['ineligible_products'],
            'applies' => $discount > 0,
        ];
    }

    public function isProductEligible(Product $product, Promotion $promotion): bool
    {
        $promotion->loadMissing(['products:id', 'categories:id', 'shops:id', 'vendors:id']);

        if (! $promotion->hasTargeting()) {
            return true;
        }

        if ($promotion->products->isNotEmpty() && ! $promotion->products->contains('id', (int) $product->id)) {
            return false;
        }

        if (
            $promotion->categories->isNotEmpty()
            && ! $promotion->categories->contains('id', (int) $product->category_id)
        ) {
            return false;
        }

        if (
            $promotion->vendors->isNotEmpty()
            && ! $promotion->vendors->contains('id', (int) $product->vendor_id)
        ) {
            return false;
        }

        if ($promotion->shops->isNotEmpty()) {
            $shopIds = $promotion->shops->pluck('id')->all();

            $existsInTargetedShops = $product->variants()
                ->whereHas('shopVariants', fn ($q) => $q->whereIn('shop_id', $shopIds))
                ->exists();

            if (! $existsInTargetedShops) {
                return false;
            }
        }

        return true;
    }

    public function resolveAutomaticFreeShippingDeliveryPrice(
        float $deliveryPrice,
        ?float $subtotal = null,
        ?Collection $items = null
    ): float {
        if ($deliveryPrice <= 0) {
            return $deliveryPrice;
        }

        if (! $this->hasActiveAutomaticFreeShipping($subtotal, $items)) {
            return $deliveryPrice;
        }

        return 0.0;
    }

    public function hasActiveAutomaticFreeShipping(?float $subtotal = null, ?Collection $items = null): bool
    {
        return $this->activeAutomaticFreeShippingPromotions($subtotal, $items)->isNotEmpty();
    }

    public function freeShippingPromotionsSnapshot(?float $subtotal = null, ?Collection $items = null): array
    {
        $normalizedItems = $this->normalizeOrderItems($items ?? collect());

        return $this->activeAutomaticFreeShippingPromotions($subtotal, $normalizedItems)
            ->map(function (Promotion $p) use ($subtotal, $normalizedItems) {
                return [
                    'promotion_id' => $p->id,
                    'type' => $p->type,
                    'min_spend' => (float) ($p->min_spend ?? 0),
                    'eligible_subtotal' => round($this->resolveEligibleSubtotal($p, $subtotal, $normalizedItems), 2),
                    'name' => $p->getTranslations('name'),
                    'description' => $p->getTranslations('description'),
                ];
            })
            ->values()
            ->all();
    }

    public function compileAutomaticPromotionsSnapshot(
        float $deliveryBeforeAutomaticFreeShipping,
        float $deliveryAfterAutomaticFreeShipping,
        array $automaticOrderPromotionsResult,
        ?float $subtotal = null,
        ?Collection $items = null
    ): ?array {
        $freeShipping = null;
        if (
            $deliveryBeforeAutomaticFreeShipping > 0
            && $deliveryAfterAutomaticFreeShipping <= 0
            && $this->hasActiveAutomaticFreeShipping($subtotal, $items)
        ) {
            $freeShipping = [
                'waived_delivery_amount' => round($deliveryBeforeAutomaticFreeShipping, 2),
                'promotions' => $this->freeShippingPromotionsSnapshot($subtotal, $items),
            ];
        }

        $gifts = $automaticOrderPromotionsResult['gifts'] ?? [];
        $pointsAwards = $automaticOrderPromotionsResult['points_awards'] ?? [];
        $pointsAwarded = (int) ($automaticOrderPromotionsResult['points_awarded'] ?? 0);

        $pointsBlock = [
            'total_awarded' => $pointsAwarded,
            'awards' => $pointsAwards,
        ];

        if ($freeShipping === null && $gifts === [] && $pointsAwarded === 0 && $pointsAwards === []) {
            return null;
        }

        return [
            'captured_at' => now()->toIso8601String(),
            'free_shipping' => $freeShipping,
            'gifts' => $gifts,
            'points' => $pointsBlock,
        ];
    }

    public function applyAutomaticOrderPromotions(
        $orderOrNull,
        ?int $userId,
        float $subtotal,
        bool $isPreview,
        ?Collection $items = null
    ): array {
        $gifts = [];
        $pointsExpected = 0;
        $pointsAwarded = 0;
        $pointsAwards = [];
        $normalizedItems = $this->normalizeOrderItems($items ?? collect());

        $promotions = Promotion::query()
            ->whereIn('type', ['spend_x_get_gift', 'spend_x_get_points'])
            ->where('is_active', true)
            ->with(['products:id', 'categories:id', 'shops:id', 'vendors:id'])
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('id')
            ->get();

        foreach ($promotions as $promotion) {
            if (! $promotion instanceof Promotion) {
                continue;
            }

            if (! $this->isPromotionActive($promotion)) {
                continue;
            }

            $eligibleSubtotal = $this->resolveEligibleSubtotal($promotion, $subtotal, $normalizedItems);

            if ($eligibleSubtotal <= 0) {
                continue;
            }

            if ($eligibleSubtotal < (float) ($promotion->min_spend ?? 0)) {
                continue;
            }

            if ($promotion->type === 'spend_x_get_gift') {
                $gifts[] = [
                    'promotion_id' => $promotion->id,
                    'promotion_title' => $promotion->name,
                    'promotion_name' => $promotion->getTranslations('name'),
                    'gift_description' => $promotion->getTranslations('gift_description'),
                    'eligible_subtotal' => round($eligibleSubtotal, 2),
                ];
            }

            if ($promotion->type === 'spend_x_get_points') {
                $pts = (int) ($promotion->reward_points ?? 0);
                if ($pts <= 0) {
                    continue;
                }

                if ($isPreview) {
                    $pointsExpected += $pts;
                    continue;
                }

                if ($orderOrNull && $userId) {
                    $awarded = $this->awardSpendXGetPointsOnce($orderOrNull, $promotion);
                    $pointsAwarded += $awarded;
                    if ($awarded > 0) {
                        $pointsAwards[] = [
                            'promotion_id' => $promotion->id,
                            'points' => $awarded,
                            'eligible_subtotal' => round($eligibleSubtotal, 2),
                            'name' => $promotion->getTranslations('name'),
                        ];
                    }
                }
            }
        }

        return [
            'gifts' => $gifts,
            'points_expected' => $pointsExpected,
            'points_awarded' => $pointsAwarded,
            'points_awards' => $pointsAwards,
        ];
    }

    protected function awardSpendXGetPointsOnce($order, Promotion $promotion): int
    {
        $points = (int) ($promotion->reward_points ?? 0);

        if ($points <= 0 || ! $order->user_id) {
            return 0;
        }

        $pointService = app(PointService::class);

        $transaction = $pointService->addPointsToWallet(
            $order->user_id,
            $points,
            null,
            'promotion',
            'earned',
            'order',
            $order->id,
            null,
            null,
            "Spend X Get Points promotion #{$promotion->id}"
        );

        return $transaction ? abs($transaction->points) : 0;
    }

    protected function isPromotionActive(Promotion $promotion): bool
    {
        if (! $promotion->is_active) {
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

    protected function calculateDiscountAmount(Promotion $promotion, float $eligibleSubtotal): float
    {
        if ($eligibleSubtotal <= 0) {
            return 0;
        }

        $discount = 0;

        switch ($promotion->discount_type) {
            case 'percentage':
                $discount = $eligibleSubtotal * ((float) $promotion->discount_value / 100);
                break;
            case 'fixed':
                $discount = (float) $promotion->discount_value;
                break;
            default:
                $discount = 0;
                break;
        }

        return round(max(0, min($discount, $eligibleSubtotal)), 2);
    }

    protected function emptyDiscountPromotionResult(
        int $promotionId,
        ?string $type = null,
        ?array $metrics = null
    ): array {
        return [
            'id' => $promotionId,
            'type' => $type,
            'eligible_subtotal' => round((float) ($metrics['eligible_subtotal'] ?? 0), 2),
            'discount' => 0.0,
            'eligible_products' => $metrics['eligible_products'] ?? [],
            'ineligible_products' => $metrics['ineligible_products'] ?? [],
            'applies' => false,
        ];
    }

    protected function normalizeOrderItems(Collection $items): Collection
    {
        return $items
            ->map(function ($item) {
                $row = is_array($item) ? $item : (array) $item;

                return [
                    'product_id' => isset($row['product_id']) ? (int) $row['product_id'] : null,
                    'category_id' => isset($row['category_id']) ? (int) $row['category_id'] : null,
                    'shop_id' => isset($row['shop_id']) ? (int) $row['shop_id'] : null,
                    'vendor_id' => isset($row['vendor_id']) ? (int) $row['vendor_id'] : null,
                    'quantity' => isset($row['quantity']) ? (float) $row['quantity'] : 0,
                    'unit_price' => isset($row['unit_price']) ? (float) $row['unit_price'] : null,
                    'subtotal' => isset($row['subtotal']) ? (float) $row['subtotal'] : null,
                ];
            })
            ->filter(fn (array $row) => ! empty($row['product_id']))
            ->values();
    }

    protected function computeEligibilityMetrics(Collection $items, Promotion $promotion): array
    {
        if ($items->isEmpty()) {
            return [
                'eligible_subtotal' => 0.0,
                'eligible_products' => [],
                'ineligible_products' => [],
                'eligible_count' => 0,
            ];
        }

        $eligibleSubtotal = 0.0;
        $eligibleProducts = [];
        $ineligibleProducts = [];
        $eligibleCount = 0;

        foreach ($items as $item) {
            if ($this->isOrderItemEligible($item, $promotion)) {
                $eligibleCount++;
                $eligibleSubtotal += $this->resolveItemSubtotal($item);
                $eligibleProducts[] = (int) $item['product_id'];
            } else {
                $ineligibleProducts[] = (int) $item['product_id'];
            }
        }

        return [
            'eligible_subtotal' => round($eligibleSubtotal, 2),
            'eligible_products' => array_values(array_unique($eligibleProducts)),
            'ineligible_products' => array_values(array_unique($ineligibleProducts)),
            'eligible_count' => $eligibleCount,
        ];
    }

    protected function isOrderItemEligible(array $item, Promotion $promotion): bool
    {
        $promotion->loadMissing(['products:id', 'categories:id', 'shops:id', 'vendors:id']);

        if (! $promotion->hasTargeting()) {
            return true;
        }

        if ($promotion->products->isNotEmpty() && ! $promotion->products->contains('id', (int) $item['product_id'])) {
            return false;
        }

        if (
            $promotion->categories->isNotEmpty()
            && ! $promotion->categories->contains('id', (int) ($item['category_id'] ?? 0))
        ) {
            return false;
        }

        if (
            $promotion->shops->isNotEmpty()
            && ! $promotion->shops->contains('id', (int) ($item['shop_id'] ?? 0))
        ) {
            return false;
        }

        if (
            $promotion->vendors->isNotEmpty()
            && ! $promotion->vendors->contains('id', (int) ($item['vendor_id'] ?? 0))
        ) {
            return false;
        }

        return true;
    }

    protected function resolveItemSubtotal(array $item): float
    {
        if (isset($item['unit_price']) && isset($item['quantity'])) {
            return round((float) $item['unit_price'] * (float) $item['quantity'], 2);
        }

        return round((float) ($item['subtotal'] ?? 0), 2);
    }

    protected function resolveEligibleSubtotal(Promotion $promotion, ?float $fallbackSubtotal, Collection $items): float
    {
        if ($items->isEmpty()) {
            return $promotion->hasTargeting() ? 0.0 : (float) ($fallbackSubtotal ?? 0);
        }

        return (float) $this->computeEligibilityMetrics($items, $promotion)['eligible_subtotal'];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Promotion>
     */
    protected function activeAutomaticFreeShippingPromotions(
        ?float $subtotal = null,
        ?Collection $items = null
    ): Collection {
        $normalizedItems = $this->normalizeOrderItems($items ?? collect());

        return Promotion::query()
            ->whereIn('type', ['free_shipping', 'spend_x_get_free_shipping'])
            ->where('is_active', true)
            ->with(['products:id', 'categories:id', 'shops:id', 'vendors:id'])
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('id')
            ->get()
            ->filter(function (Promotion $promotion) use ($subtotal, $normalizedItems) {
                $eligibleSubtotal = $this->resolveEligibleSubtotal($promotion, $subtotal, $normalizedItems);

                if ($eligibleSubtotal <= 0) {
                    return false;
                }

                $minSpend = (float) ($promotion->min_spend ?? 0);
                if ($minSpend > 0 && $eligibleSubtotal < $minSpend) {
                    return false;
                }

                return in_array($promotion->type, ['free_shipping', 'spend_x_get_free_shipping'], true);
            })
            ->values();
    }
}
