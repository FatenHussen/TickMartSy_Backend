<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PointWallet;
use App\Models\PointExchange;
use App\Models\Subscription;
use App\Models\VendorSubscription;

class UserProfileSummaryController extends Controller
{
    public function __invoke()
    {
        $user = auth('user')->user();

        // Get user points
        $wallet = PointWallet::where('user_id', $user->id)->first();
        $points = $wallet ? $wallet->balance : 0;

        // Get active gifts count (completed point exchanges)
        $giftsCount = PointExchange::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // Get active subscription name
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->with('package')
            ->first();

        $subscriptionName = $subscription && $subscription->package
            ? $subscription->package->name
            : null;

        return $this->sendResponse(data: [
            'points' => $points,
            'gifts_count' => $giftsCount,
            'subscription_name' => $subscriptionName,
        ]);
    }
}
