<?php

namespace App\Services\User;

use App\Models\PointExchange;
use App\Models\PointWallet;
use App\Models\Subscription;

class UserProfileSummaryService
{
    public function query(int $userId): array
    {
        $points = PointWallet::where('user_id', $userId)->value('balance') ?? 0;

        $giftsCount = PointExchange::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        $subscription = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->with('package')
            ->first();

        return [
            'points' => $points,
            'gifts_count' => $giftsCount,
            'subscription_name' => $subscription?->package?->name,
        ];
    }
}
