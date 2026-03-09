<?php

namespace App\Services\User;

use App\Models\Subscription;
use App\Models\SubscriptionUsageLog;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionBenefitsService
{

    public function applyBenefits(
        ?Order $order,
        int $userId,
        float $subtotal,
        float $deliveryPrice,
        bool $useDiscount = false,
        bool $useFreeDelivery = false
    ): array {

        $benefits = $this->calculateBenefits(
            $userId,
            $subtotal,
            $deliveryPrice,
            $useDiscount,
            $useFreeDelivery
        );

        if (!$benefits['has_subscription']) {
            return $benefits;
        }

        $subscription = $benefits['subscription'];

        if ($order && $benefits['discount_amount'] > 0) {

            $this->logUsage(
                $subscription,
                $order,
                'discount',
                $benefits['discount_amount'],
                'Subscription discount applied'
            );

            if (!is_null($subscription->remaining_orders)) {
                $subscription->decrement('remaining_orders');
            }
        }

        if ($order && $benefits['free_delivery_applied']) {

            $this->logUsage(
                $subscription,
                $order,
                'free_delivery',
                $deliveryPrice,
                'Subscription free delivery applied'
            );

            $subscription->decrement('remaining_free_deliveries');
        }

        return $this->formatResponse($subscription, $benefits);
    }

    public function previewBenefits(
        int $userId,
        float $subtotal,
        float $deliveryPrice
    ): array {

        $benefits = $this->calculateBenefits(
            $userId,
            $subtotal,
            $deliveryPrice,
            true,
            true
        );

        if (!$benefits['has_subscription']) {

            return [
                'has_subscription' => false,
                'discount_amount' => 0,
                'free_delivery_applicable' => false,
            ];
        }

        return [
            'has_subscription' => true,
            'subscription_id' => $benefits['subscription']->id,
            'package_name' => $benefits['package']->name,
            'discount_percentage' =>
            $benefits['package']->discount_percentage,
            'discount_amount' => $benefits['discount_amount'],
            'free_delivery_applicable' =>
            $benefits['free_delivery_applicable'],
        ];
    }

    private function calculateBenefits(
        int $userId,
        float $subtotal,
        float $deliveryPrice,
        bool $useDiscount,
        bool $useFreeDelivery
    ): array {

        $subscription = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (!$subscription || !$subscription->isActive()) {

            return [
                'has_subscription' => false,
                'discount_amount' => 0,
                'free_delivery_applied' => false,
            ];
        }

        $package = $subscription->package;

        $discountAmount = 0;

        if ($useDiscount && $package->discount_percentage > 0) {

            $discountAmount =
                $subtotal * ($package->discount_percentage / 100);
        }

        $freeDeliveryApplicable =
            $useFreeDelivery &&
            $subscription->hasRemainingFreeDeliveries() &&
            $deliveryPrice > 0;

        return [
            'has_subscription' => true,
            'subscription' => $subscription,
            'package' => $package,
            'discount_amount' => round($discountAmount, 2),
            'free_delivery_applied' => $freeDeliveryApplicable,
            'free_delivery_applicable' => $freeDeliveryApplicable,
        ];
    }

    private function formatResponse(
        Subscription $subscription,
        array $benefits
    ): array {

        return [
            'has_subscription' => true,
            'subscription_id' => $subscription->id,
            'package_name' => $subscription->package->name,
            'discount_amount' => $benefits['discount_amount'],
            'free_delivery_applied' => $benefits['free_delivery_applied'],
            'remaining_orders' =>
            $subscription->fresh()->remaining_orders,
            'remaining_free_deliveries' =>
            $subscription->fresh()->remaining_free_deliveries,
        ];
    }


    /**
     * تسجيل استخدام ميزة من الاشتراك
     */
    protected function logUsage(
        Subscription $subscription,
        Order $order,
        string $usageType,
        float $value,
        string $notes = null
    ): void {
        SubscriptionUsageLog::create([
            'subscription_id' => $subscription->id,
            'order_id' => $order->id,
            'usage_type' => $usageType,
            'value' => $value,
            'remaining_orders_before' => $subscription->remaining_orders,
            'remaining_orders_after' => $usageType === 'discount'
                ? ($subscription->remaining_orders ? $subscription->remaining_orders - 1 : null)
                : $subscription->remaining_orders,
            'remaining_free_deliveries_before' => $subscription->remaining_free_deliveries,
            'remaining_free_deliveries_after' => $usageType === 'free_delivery'
                ? $subscription->remaining_free_deliveries - 1
                : $subscription->remaining_free_deliveries,
            'notes' => $notes,
        ]);
    }

    /**
     * الحصول على إحصائيات استخدام الاشتراك
     */
    public function getUsageStats(int $subscriptionId): array
    {
        $logs = SubscriptionUsageLog::where('subscription_id', $subscriptionId)->get();

        return [
            'total_discount_used' => $logs->where('usage_type', 'discount')->sum('value'),
            'total_free_deliveries_used' => $logs->where('usage_type', 'free_delivery')->count(),
            'total_orders' => $logs->pluck('order_id')->unique()->count(),
        ];
    }
}
