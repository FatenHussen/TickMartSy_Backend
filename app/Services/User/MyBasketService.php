<?php

namespace App\Services\User;

use App\Enums\CartType;
use App\Http\Resources\Basket\BasketSummaryResource;
use App\Http\Resources\User\MyBasket\MyBasketResource;
use App\Models\Basket;
use App\Models\Order;
use App\Models\ScheduledBasketAlert;
use App\Models\UserBasketSchedule;
use App\Services\ScheduledBasketAlertService;
use App\Services\ScheduledBasketAvailabilityService;
use Illuminate\Support\Collection;

class MyBasketService
{
    public function getMyBaskets(int $userId, ?string $type = 'all')
    {
        $results = collect();

        if (in_array($type, ['user-schedule', 'all'])) {
            $userScheduledBaskets = UserBasketSchedule::where('user_id', $userId)
                ->with([
                    'schedule',
                    'items.product.media',
                    'items.variant.productVariant',
                ])
                ->withCount('items')
                ->get()
                ->map(function ($basket) {
                    $basket->basket_type = 'user-schedule';
                    return $basket;
                });

            $results = $results->merge($userScheduledBaskets);
        }

        if (in_array($type, ['subscription', 'all'])) {
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
                    'items.shopProductVariant.productVariant.product',
                    'schedules',
                ])
                ->get()
                ->map(function ($basket) use ($orders) {
                    $basket->basket_type = 'subscription';

                    $latestOrder = $orders->where('basket_id', $basket->id)
                        ->sortByDesc('created_at')
                        ->first();

                    $basket->selected_schedule = $latestOrder?->basketSchedule;
                    $basket->pause_at = $latestOrder?->pause_at;
                    $basket->is_paused = $latestOrder?->pause_at !== null;
                    $basket->paused_at = $latestOrder?->pause_at;
                    $basket->next_run_date = $latestOrder?->next_run_date;
                    $basket->latest_order = $latestOrder;

                    return $basket;
                });

            $results = $results->merge($subscriptionBaskets);
        }

        if (in_array($type, ['custom', 'all'])) {
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

        $results = $this->decorateBaskets($results, $userId);

        return $results->map(function ($basket) {
            if ($basket->basket_type === 'user-schedule') {
                return (new MyBasketResource($basket))->resolve();
            }

            return (new BasketSummaryResource($basket))->resolve();
        })->values();
    }

    public function pauseSubscriptionBasket(int $userId, int $basketId)
    {
        Order::where('basket_id', $basketId)
            ->where('user_id', $userId)
            ->where('cart_type', CartType::SCHEDULE_ADMIN_CART->value)
            ->update(['pause_at' => now()]);
    }

    public function resumeSubscriptionBasket(int $userId, int $basketId)
    {
        Order::where('basket_id', $basketId)
            ->where('user_id', $userId)
            ->where('cart_type', CartType::SCHEDULE_ADMIN_CART->value)
            ->update(['pause_at' => null]);
    }

    private function decorateBaskets(Collection $baskets, int $userId): Collection
    {
        $availabilityService = app(ScheduledBasketAvailabilityService::class);
        $alertService = app(ScheduledBasketAlertService::class);

        return $baskets->map(function ($basket) use ($availabilityService, $alertService, $userId) {
            if ($basket->basket_type === 'user-schedule') {
                $summary = $availabilityService->evaluateUserSchedule($basket);
                $basket->availability_summary = $summary;
                $basket->active_alert = $alertService->getOpenAlert(
                    ScheduledBasketAlert::BASKET_TYPE_USER_SCHEDULE,
                    $userId,
                    $basket->id,
                );

                return $basket;
            }

            if ($basket->basket_type === 'subscription') {
                $summary = $availabilityService->evaluateAdminSchedule(
                    $basket,
                    $basket->latest_order ?? null,
                );

                $basket->availability_summary = $summary;
                $basket->active_alert = $alertService->getOpenAlert(
                    ScheduledBasketAlert::BASKET_TYPE_ADMIN_SCHEDULE,
                    $userId,
                    $basket->id,
                );
            }

            return $basket;
        });
    }
}
