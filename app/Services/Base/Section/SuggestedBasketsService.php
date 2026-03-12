<?php

namespace App\Services\Base\Section;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Basket;

class SuggestedBasketsService
{
    public function query(array $filters = [])
    {
        $userId = auth('user')->id();
        $deliveredStatuses = [
            OrderStatus::DELIVERED->value,
            'completed',
        ];

        if ($userId) {
            $userTotals = Order::query()
                ->join('baskets', 'baskets.id', '=', 'orders.basket_id')
                ->where('orders.user_id', $userId)
                ->whereIn('orders.status', $deliveredStatuses)
                ->where('baskets.is_schedule', 0)
                ->selectRaw('orders.basket_id, COUNT(*) as total')
                ->groupBy('orders.basket_id');

            if ((clone $userTotals)->limit(1)->exists()) {
                return Basket::query()
                    ->where('is_schedule', 0)
                    ->select('baskets.*')
                    ->joinSub($userTotals, 'user_baskets', function ($join) {
                        $join->on('baskets.id', '=', 'user_baskets.basket_id');
                    })
                    ->orderByDesc('user_baskets.total');
            }
        }

        $globalTotals = Order::query()
            ->join('baskets', 'baskets.id', '=', 'orders.basket_id')
            ->whereIn('orders.status', $deliveredStatuses)
            ->where('baskets.is_schedule', 0)
            ->selectRaw('orders.basket_id, COUNT(*) as total')
            ->groupBy('orders.basket_id');

        if ((clone $globalTotals)->limit(1)->exists()) {
            return Basket::query()
                ->where('is_schedule', 0)
                ->select('baskets.*')
                ->joinSub($globalTotals, 'global_baskets', function ($join) {
                    $join->on('baskets.id', '=', 'global_baskets.basket_id');
                })
                ->orderByDesc('global_baskets.total');
        }

        return Basket::query()
            ->where('is_schedule', 0)
            ->latest();
    }
}
