<?php

namespace App\Services\User;

use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function subscribe(User $user, Package $package, bool $isRenew = false): Subscription
    {
        return DB::transaction(function () use ($user, $package, $isRenew) {

            $current = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($isRenew && $current && $current->package_id === $package->id) {

                $current->update([
                    'start_date' => now(),
                    'end_date' => now()->addDays($package->duration_days),
                    'remaining_orders' => $package->monthly_orders_limit,
                    'remaining_free_deliveries' => $package->free_delivery_count,
                ]);

                $subscription = $current;
            } else {
                Subscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->update(['status' => 'cancelled']);

                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'start_date' => now(),
                    'end_date' => now()->addDays($package->duration_days),
                    'remaining_orders' => $package->monthly_orders_limit,
                    'remaining_free_deliveries' => $package->free_delivery_count,
                    'type' => $isRenew ? 'renew' : 'new',
                ]);
            }

            // Award package points bonus
            if ($package->points_bonus > 0) {
                $pointService = app(\App\Services\PointService::class);

                $pointService->addPointsToWallet(
                    userId: $user->id,
                    points: $package->points_bonus,
                    ruleId: null,
                    source: 'subscription_package',
                    status: 'earned',
                    referenceType: 'subscription',
                    referenceId: $subscription->id,
                    expiresAfterDays: 365,
                    reason: "Package subscription bonus: {$package->name}"
                );
            }

            return $subscription;
        });
    }
}
