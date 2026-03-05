<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PointExchange;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;

class ActiveBenefitsController extends Controller
{
    /**
     * Get all active benefits (point exchanges + subscription benefits)
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = auth('user')->user();

        if (!$user) {
            return $this->sendError('المستخدم غير مسجل دخول', [], 401);
        }

        $coupons = [];
        $freeDeliveries = [];

        // 1. Get active point exchanges for coupons
        $activeCouponExchanges = PointExchange::where('user_id', $user->id)
            ->where('exchange_type', 'coupon')
            ->where('status', 'completed')
            ->whereRaw("JSON_EXTRACT(exchange_data, '$.expires_at') >= ?", [now()->toDateString()])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($activeCouponExchanges as $exchange) {
            $coupons[] = [
                'key' => 'point_coupon_exchange_id',
                'value' => $exchange->id,
                'title' => 'خصم من النقاط',
                'discount_amount' => $exchange->exchange_data['discount_amount'] ?? 0,
                'expired_at' => $exchange->exchange_data['expires_at'] ?? null,
            ];
        }

        // 2. Get active point exchanges for free delivery
        $activeFreeDeliveryExchanges = PointExchange::where('user_id', $user->id)
            ->where('exchange_type', 'free_delivery')
            ->where('status', 'completed')
            ->whereRaw("JSON_EXTRACT(exchange_data, '$.expires_at') >= ?", [now()->toDateString()])
            ->orderBy('created_at', 'asc') // أقرب واحد ينتهي أولاً
            ->get();

        foreach ($activeFreeDeliveryExchanges as $exchange) {
            $freeDeliveries[] = [
                'key' => 'point_free_delivery_exchange_id',
                'value' => $exchange->id,
                'title' => 'توصيل مجاني من النقاط',
                'expired_at' => $exchange->exchange_data['expires_at'] ?? null,
            ];
        }

        // 3. Get active subscription benefits
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($subscription && $subscription->isActive()) {
            // Add subscription discount if available
            if ($subscription->remaining_orders > 0) {
                $coupons[] = [
                    'key' => 'use_subscription_discount',
                    'value' => true,
                    'title' => 'خصم من الباقة',
                    'discount_percentage' => $subscription->package->discount_percentage ?? 0,
                    'expired_at' => $subscription->end_date->toDateString(),
                ];
            }

            // Add subscription free delivery if available
            if ($subscription->remaining_free_deliveries > 0) {
                $freeDeliveries[] = [
                    'key' => 'use_subscription_free_delivery',
                    'value' => true,
                    'title' => 'توصيل مجاني من الباقة',
                    'remaining_count' => $subscription->remaining_free_deliveries,
                    'expired_at' => $subscription->end_date->toDateString(),
                ];
            }
        }

        return $this->sendResponse([
            'coupons' => $coupons,
            'free_deliveries' => $freeDeliveries,
            'has_benefits' => count($coupons) > 0 || count($freeDeliveries) > 0,
        ]);
    }
}
