<?php

namespace App\Services\User;

use App\Models\Subscription;
use App\Models\SubscriptionUsageLog;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionBenefitsService
{
    /**
     * تطبيق مزايا الاشتراك على الأوردر
     *
     * @param Order $order
     * @param int $userId
     * @param float $subtotal
     * @param float $deliveryPrice
     * @param bool $useDiscount هل يريد المستخدم استخدام خصم الباقة؟
     * @param bool $useFreeDelivery هل يريد المستخدم استخدام توصيل مجاني من الباقة؟
     */
    public function applyBenefits(
        Order $order,
        int $userId,
        float $subtotal,
        float $deliveryPrice,
        bool $useDiscount = false,
        bool $useFreeDelivery = false
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
        if (!$package) {
            return [
                'has_subscription' => false,
                'discount_amount' => 0,
                'free_delivery_applied' => false,
            ];
        }

        // التحقق من حد الأوردرات
        if (!$subscription->hasRemainingOrders()) {
            Log::warning('Subscription order limit reached', [
                'subscription_id' => $subscription->id,
                'user_id' => $userId
            ]);

            return [
                'has_subscription' => true,
                'order_limit_reached' => true,
                'discount_amount' => 0,
                'free_delivery_applied' => false,
            ];
        }

        // إذا لم يطلب المستخدم أي ميزة، لا نطبق شيء
        if (!$useDiscount && !$useFreeDelivery) {
            return [
                'has_subscription' => true,
                'subscription_id' => $subscription->id,
                'package_name' => $package->name,
                'discount_amount' => 0,
                'free_delivery_applied' => false,
                'user_chose_not_to_use' => true,
            ];
        }

        $discountAmount = 0;
        $freeDeliveryApplied = false;

        // 1. تطبيق خصم الباقة (فقط إذا طلب المستخدم)
        if ($useDiscount && $package->discount_percentage > 0) {
            $discountAmount = $subtotal * ($package->discount_percentage / 100);

            $this->logUsage(
                $subscription,
                $order,
                'discount',
                $discountAmount,
                'تطبيق خصم الباقة ' . $package->discount_percentage . '%'
            );
        }

        // 2. تطبيق التوصيل المجاني (فقط إذا طلب المستخدم)
        if ($useFreeDelivery && $subscription->hasRemainingFreeDeliveries() && $deliveryPrice > 0) {
            $freeDeliveryApplied = true;

            $this->logUsage(
                $subscription,
                $order,
                'free_delivery',
                $deliveryPrice,
                'تطبيق توصيل مجاني من الباقة'
            );

            // تقليل عدد التوصيلات المجانية المتبقية
            $subscription->decrement('remaining_free_deliveries');
        }

        // تقليل عدد الأوردرات المتبقية (فقط إذا استخدم خصم)
        if ($useDiscount && !is_null($subscription->remaining_orders)) {
            $subscription->decrement('remaining_orders');
        }

        return [
            'has_subscription' => true,
            'subscription_id' => $subscription->id,
            'package_name' => $package->name,
            'discount_amount' => round($discountAmount, 2),
            'free_delivery_applied' => $freeDeliveryApplied,
            'remaining_orders' => $subscription->fresh()->remaining_orders,
            'remaining_free_deliveries' => $subscription->fresh()->remaining_free_deliveries,
        ];
    }

    /**
     * معاينة مزايا الاشتراك بدون تطبيقها
     */
    public function previewBenefits(int $userId, float $subtotal, float $deliveryPrice): array
    {
        $subscription = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (!$subscription || !$subscription->isActive()) {
            return [
                'has_subscription' => false,
                'discount_amount' => 0,
                'free_delivery_applicable' => false,
            ];
        }

        $package = $subscription->package;
        if (!$package) {
            return [
                'has_subscription' => false,
                'discount_amount' => 0,
                'free_delivery_applicable' => false,
            ];
        }

        if (!$subscription->hasRemainingOrders()) {
            return [
                'has_subscription' => true,
                'order_limit_reached' => true,
                'discount_amount' => 0,
                'free_delivery_applicable' => false,
            ];
        }

        $discountAmount = 0;
        if ($package->discount_percentage > 0) {
            $discountAmount = $subtotal * ($package->discount_percentage / 100);
        }

        $freeDeliveryApplicable = $subscription->hasRemainingFreeDeliveries() && $deliveryPrice > 0;

        return [
            'has_subscription' => true,
            'subscription_id' => $subscription->id,
            'package_name' => $package->name,
            'discount_percentage' => $package->discount_percentage,
            'discount_amount' => round($discountAmount, 2),
            'free_delivery_applicable' => $freeDeliveryApplicable,
            'remaining_orders' => $subscription->remaining_orders,
            'remaining_free_deliveries' => $subscription->remaining_free_deliveries,
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
