<?php

namespace App\Services\Base\Section;

use App\Models\Order;
use App\Models\Basket;

class SuggestedBasketsService
{
    public function query(array $filters = [])
    {
        $userId = auth('user')->id();

        $basketIds = Order::query()
            ->where('user_id', $userId)
            ->whereNotNull('basket_id')
            ->selectRaw('basket_id, COUNT(*) as total')
            ->groupBy('basket_id')
            ->orderByDesc('total')
            ->pluck('basket_id');

        if ($basketIds->isEmpty()) {
            return Basket::query()->inRandomOrder();
        }

        return Basket::query()->where('is_schedule', 0)->whereIn('id', $basketIds);
    }
}
