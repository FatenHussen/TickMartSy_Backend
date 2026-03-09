<?php

namespace App\Services\User;

use App\Http\Resources\User\MyBasket\MyBasketResource;
use App\Http\Resources\Basket\OneResource as BasketOneResource;
use App\Models\UserBasketSchedule;
use App\Models\Order;
use App\Models\Basket;
use App\Enums\CartType;
use App\Http\Resources\Basket\BasketSummaryResource;
use Illuminate\Support\Collection;

class MyBasketService
{
    public function getMyBaskets(int $userId, ?string $type = 'all')
    {
        $results = collect();

        // 1. User's scheduled baskets (UserBasketSchedule)
        if (in_array($type, ['user-schedule', 'all'])) {
            $userScheduledBaskets = UserBasketSchedule::where('user_id', $userId)
                ->with([
                    'schedule',
                    'items.product.media',
                    'items.variant.productVariant'
                ])
                ->withCount('items')
                ->get()
                ->map(function ($basket) {
                    $basket->basket_type = 'user-schedule';
                    return $basket;
                });

            $results = $results->merge($userScheduledBaskets);
        }

        // 2. Admin's scheduled baskets from Orders (cart_type = schedule_admin_cart)
        if (in_array($type, ['subscription', 'all'])) {
            // Get baskets with selected schedule from orders
            $orders = Order::where('user_id', $userId)
                ->where('cart_type', CartType::SCHEDULE_ADMIN_CART->value)
                ->whereNotNull('basket_id')
                ->with('basketSchedule')
                ->get();

            $basketIds = $orders->pluck('basket_id')->unique();

            $subscriptionBaskets = Basket::whereIn('id', $basketIds)
                ->with([
                    'category',
                    'items.product.media',
                    'items.variant',
                    'schedules'
                ])
                ->get()
                ->map(function ($basket) use ($orders) {
                    $basket->basket_type = 'subscription';
                    // Find the selected schedule from order
                    $order = $orders->firstWhere('basket_id', $basket->id);
                    $basket->selected_schedule = $order?->basketSchedule;
                    $basket->pause_at = $order?->pause_at;
                    return $basket;
                });

            $results = $results->merge($subscriptionBaskets);
        }

        // 3. Admin's regular baskets from Orders (cart_type = admin_cart)
        if (in_array($type, ['custom', 'all'])) {
            // Get unique basket IDs from orders
            $basketIds = Order::where('user_id', $userId)
                ->where('cart_type', CartType::ADMIN_CART->value)
                ->whereNotNull('basket_id')
                ->pluck('basket_id')
                ->unique();

            $customBaskets = Basket::whereIn('id', $basketIds)
                ->with([
                    'category',
                    'items.product.media',
                    'items.variant',
                ])
                ->get()
                ->map(function ($basket) {
                    $basket->basket_type = 'custom';
                    return $basket;
                });

            $results = $results->merge($customBaskets);
        }

        // Return appropriate resource based on basket type
        return $results->map(function ($basket) {
            if ($basket->basket_type === 'user-schedule') {
                return (new MyBasketResource($basket))->resolve();
            } else {
                return (new BasketSummaryResource($basket))->resolve();
            }
        })->values();
    }
    public function pauseSubscriptionBasket(int $userId, int $basketId)
    {
        Order::where('basket_id', $basketId)
            ->where('user_id', $userId)
            ->where('cart_type', CartType::SCHEDULE_ADMIN_CART->value)
            ->update([
                'pause_at' => now()
            ]);
    }
    public function resumeSubscriptionBasket(int $userId, int $basketId)
    {
        Order::where('basket_id', $basketId)
            ->where('user_id', $userId)
            ->where('cart_type', CartType::SCHEDULE_ADMIN_CART->value)
            ->update([
                'pause_at' => null
            ]);
    }
}
