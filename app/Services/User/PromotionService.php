<?php

namespace App\Services\User;

use App\Models\Promotion;
use App\Services\PointService;
use Illuminate\Support\Collection;

class PromotionService
{
    /** أنواع يختارها المستخدم عبر promotion_id فقط */
    public const USER_SELECTABLE_TYPES = ['simple_discount', 'spend_x_discount'];

    /** تُطبَّق تلقائياً عند استيفاء الشروط (بدون promotion_id) */
    public const AUTOMATIC_TYPES = ['spend_x_get_gift', 'spend_x_get_points', 'free_shipping'];

    /**
     * عروض يمكن للمستخدم اختيار واحد منها مع الطلب (خصم فقط).
     */
    public function getAvailablePromotions(float $subtotal, Collection $items): Collection
    {
        return Promotion::query()
            ->whereIn('type', self::USER_SELECTABLE_TYPES)
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
                if ($promotion->type === 'simple_discount') {
                    return true;
                }
                if ($promotion->type === 'spend_x_discount') {
                    return $subtotal >= ($promotion->min_spend ?? 0);
                }

                return false;
            })
            ->values()
            ->map(fn(Promotion $promotion) => [
                'id' => $promotion->id,
                'name' => $promotion->name,
                'description' => $promotion->description,
            ]);
    }

    /**
     * خصم من عرض يختاره المستخدم (simple_discount أو spend_x_discount فقط).
     */
    public function applyDiscountPromotion($orderOrNull, int $promotionId, float $subtotal): float
    {
        $promotion = Promotion::find($promotionId);

        if (
            !$promotion
            || !$this->isPromotionActive($promotion)
            || !in_array($promotion->type, self::USER_SELECTABLE_TYPES, true)
        ) {
            return 0;
        }

        if ($promotion->type === 'spend_x_discount' && $subtotal < ($promotion->min_spend ?? 0)) {
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

    /**
     * توصيل مجاني تلقائياً إذا وُجد عرض free_shipping نشط ضمن التواريخ (دون اختيار المستخدم).
     */
    public function resolveAutomaticFreeShippingDeliveryPrice(float $deliveryPrice): float
    {
        if ($deliveryPrice <= 0) {
            return $deliveryPrice;
        }

        if (! $this->hasActiveAutomaticFreeShipping()) {
            return $deliveryPrice;
        }

        return 0.0;
    }

    /**
     * هل يوجد حالياً عرض توصيل مجاني تلقائي مفعّل (للمعاينة).
     */
    public function hasActiveAutomaticFreeShipping(): bool
    {
        return Promotion::query()
            ->where('type', 'free_shipping')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->exists();
    }

    /**
     * العروض النشطة من نوع free_shipping (للقطة JSON على الطلب).
     *
     * @return array<int, array{promotion_id: int, name: array, description: array}>
     */
    public function freeShippingPromotionsSnapshot(): array
    {
        return Promotion::query()
            ->where('type', 'free_shipping')
            ->where('is_active', true)
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
            ->map(fn(Promotion $p) => [
                'promotion_id' => $p->id,
                'name' => $p->getTranslations('name'),
                'description' => $p->getTranslations('description'),
            ])
            ->values()
            ->all();
    }

    /**
     * لقطة للعروض التلقائية المطبّقة على الطلب (تُخزَّن في orders.automatic_promotions_snapshot).
     */
    public function compileAutomaticPromotionsSnapshot(
        float $deliveryBeforeAutomaticFreeShipping,
        float $deliveryAfterAutomaticFreeShipping,
        array $automaticOrderPromotionsResult
    ): ?array {
        $freeShipping = null;
        if (
            $deliveryBeforeAutomaticFreeShipping > 0
            && $deliveryAfterAutomaticFreeShipping <= 0
            && $this->hasActiveAutomaticFreeShipping()
        ) {
            $freeShipping = [
                'waived_delivery_amount' => round($deliveryBeforeAutomaticFreeShipping, 2),
                'promotions' => $this->freeShippingPromotionsSnapshot(),
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

    /**
     * تطبيق عروض الهدايا والنقاط التلقائية (معاينة أو بعد إنشاء الطلب).
     *
     * @return array{gifts: array<int, array>, points_expected: int, points_awarded: int, points_awards: array<int, array>}
     */
    public function applyAutomaticOrderPromotions(
        $orderOrNull,
        ?int $userId,
        float $subtotal,
        bool $isPreview
    ): array {
        $gifts = [];
        $pointsExpected = 0;
        $pointsAwarded = 0;
        $pointsAwards = [];

        $promotions = Promotion::query()
            ->whereIn('type', ['spend_x_get_gift', 'spend_x_get_points'])
            ->where('is_active', true)
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
            if (! $this->isPromotionActive($promotion)) {
                continue;
            }

            if ($subtotal < ($promotion->min_spend ?? 0)) {
                continue;
            }

            if ($promotion->type === 'spend_x_get_gift') {
                $gifts[] = [
                    'promotion_id' => $promotion->id,
                    'promotion_title' => $promotion->name,
                    'promotion_name' => $promotion->getTranslations('name'),
                    'gift_description' => $promotion->getTranslations('gift_description'),
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
}
