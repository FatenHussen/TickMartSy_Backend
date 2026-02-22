<?php

namespace Database\Seeders;

use App\Models\PointTransaction;
use App\Models\PointWallet;
use App\Models\PointRule;
use App\Models\Gift;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PointTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wallets = PointWallet::with('user')->get();
        $rules = PointRule::all()->keyBy('code');
        
        foreach ($wallets as $wallet) {
            $currentBalance = 0;

            // 1. Registration bonus
            if ($rules->has('user_registration')) {
                $transaction = PointTransaction::create([
                    'user_id' => $wallet->user_id,
                    'wallet_id' => $wallet->id,
                    'rule_id' => $rules['user_registration']->id,
                    'created_by_admin_id' => null,
                    'source' => 'user_registration',
                    'points' => $rules['user_registration']->value,
                    'status' => 'earned',
                    'reference_type' => 'user',
                    'reference_id' => $wallet->user_id,
                    'expires_at' => now()->addDays($rules['user_registration']->expires_after_days),
                    'reason' => null,
                    'created_at' => $wallet->user->created_at,
                    'updated_at' => $wallet->user->created_at,
                ]);
                $currentBalance += $transaction->points;
            }

            // 2. Random order transactions
            $orderCount = rand(2, 6);
            for ($i = 0; $i < $orderCount; $i++) {
                $isFirstOrder = $i === 0;
                $orderValue = rand(15, 120);
                $createdAt = Carbon::now()->subDays(rand(1, 50));

                // First order bonus
                if ($isFirstOrder && $rules->has('first_order')) {
                    $transaction = PointTransaction::create([
                        'user_id' => $wallet->user_id,
                        'wallet_id' => $wallet->id,
                        'rule_id' => $rules['first_order']->id,
                        'created_by_admin_id' => null,
                        'source' => 'first_order',
                        'points' => $rules['first_order']->value,
                        'status' => 'earned',
                        'reference_type' => 'order',
                        'reference_id' => 1000 + $i,
                        'expires_at' => $createdAt->copy()->addDays($rules['first_order']->expires_after_days),
                        'reason' => null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    $currentBalance += $transaction->points;
                }

                // Order completion points
                if ($rules->has('order_completion')) {
                    $points = (int) floor(($orderValue * $rules['order_completion']->value) / 100);
                    if ($points > 0) {
                        $transaction = PointTransaction::create([
                            'user_id' => $wallet->user_id,
                            'wallet_id' => $wallet->id,
                            'rule_id' => $rules['order_completion']->id,
                            'created_by_admin_id' => null,
                            'source' => 'order_completion',
                            'points' => $points,
                            'status' => 'earned',
                            'reference_type' => 'order',
                            'reference_id' => 1000 + $i,
                            'expires_at' => $createdAt->copy()->addDays($rules['order_completion']->expires_after_days),
                            'reason' => null,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                        $currentBalance += $transaction->points;
                    }
                }

                // Large order bonus (random chance)
                if ($orderValue >= 100 && $rules->has('large_order_bonus') && rand(1, 3) === 1) {
                    $transaction = PointTransaction::create([
                        'user_id' => $wallet->user_id,
                        'wallet_id' => $wallet->id,
                        'rule_id' => $rules['large_order_bonus']->id,
                        'created_by_admin_id' => null,
                        'source' => 'large_order_bonus',
                        'points' => $rules['large_order_bonus']->value,
                        'status' => 'earned',
                        'reference_type' => 'order',
                        'reference_id' => 1000 + $i,
                        'expires_at' => $createdAt->copy()->addDays($rules['large_order_bonus']->expires_after_days),
                        'reason' => null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    $currentBalance += $transaction->points;
                }
            }

            // 3. Random review points
            $reviewCount = rand(1, 3);
            for ($i = 0; $i < $reviewCount; $i++) {
                if ($rules->has('product_review')) {
                    $createdAt = Carbon::now()->subDays(rand(1, 40));
                    
                    $transaction = PointTransaction::create([
                        'user_id' => $wallet->user_id,
                        'wallet_id' => $wallet->id,
                        'rule_id' => $rules['product_review']->id,
                        'created_by_admin_id' => null,
                        'source' => 'product_review',
                        'points' => $rules['product_review']->value,
                        'status' => 'earned',
                        'reference_type' => 'review',
                        'reference_id' => 2000 + $i,
                        'expires_at' => $createdAt->copy()->addDays($rules['product_review']->expires_after_days),
                        'reason' => null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    $currentBalance += $transaction->points;
                }
            }

            // 4. Some manual admin transactions (random)
            if (rand(1, 4) === 1) {
                $isAddition = rand(1, 2) === 1;
                $points = $isAddition ? rand(10, 50) : -rand(5, 25);
                $createdAt = Carbon::now()->subDays(rand(1, 15));
                
                $transaction = PointTransaction::create([
                    'user_id' => $wallet->user_id,
                    'wallet_id' => $wallet->id,
                    'rule_id' => null,
                    'created_by_admin_id' => 1, // Assume admin exists
                    'source' => $isAddition ? 'manual_addition' : 'manual_deduction',
                    'points' => $points,
                    'status' => 'earned',
                    'reference_type' => null,
                    'reference_id' => null,
                    'expires_at' => null,
                    'reason' => $isAddition ? 'Bonus for loyal customer' : 'Adjustment for refund',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $currentBalance += $transaction->points;
            }

            // 5. Some pending transactions (random)
            if (rand(1, 3) === 1) {
                $points = rand(10, 25);
                $createdAt = Carbon::now()->subDays(rand(1, 3));
                
                PointTransaction::create([
                    'user_id' => $wallet->user_id,
                    'wallet_id' => $wallet->id,
                    'rule_id' => $rules->has('order_completion') ? $rules['order_completion']->id : null,
                    'created_by_admin_id' => null,
                    'source' => 'order_completion',
                    'points' => $points,
                    'status' => 'pending',
                    'reference_type' => 'order',
                    'reference_id' => 9999, // Fake pending order
                    'expires_at' => $createdAt->copy()->addDays(365),
                    'reason' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                // Don't add pending points to balance
            }

            // 6. Some redeemed transactions - كوبون/خصم (random)
            if (rand(1, 4) === 1 && $currentBalance > 20) {
                $redeemedPoints = rand(10, min(30, $currentBalance));
                $createdAt = Carbon::now()->subDays(rand(1, 20));
                
                PointTransaction::create([
                    'user_id' => $wallet->user_id,
                    'wallet_id' => $wallet->id,
                    'rule_id' => null,
                    'created_by_admin_id' => null,
                    'source' => 'redemption',
                    'points' => -$redeemedPoints,
                    'status' => 'redeemed',
                    'reference_type' => 'discount',
                    'reference_id' => rand(1000, 9999),
                    'expires_at' => null,
                    'reason' => 'Redeemed for order discount',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $currentBalance -= $redeemedPoints;
            }

            // 7. استبدال بهدية - Gift redemption (random)
            $gift = Gift::orderBy('points_required')->first();
            if ($gift && rand(1, 5) === 1 && $currentBalance >= $gift->points_required) {
                $createdAt = Carbon::now()->subDays(rand(1, 15));
                
                PointTransaction::create([
                    'user_id' => $wallet->user_id,
                    'wallet_id' => $wallet->id,
                    'rule_id' => null,
                    'created_by_admin_id' => null,
                    'source' => 'gift_exchange',
                    'points' => -$gift->points_required,
                    'status' => 'redeemed',
                    'reference_type' => 'gift',
                    'reference_id' => $gift->id,
                    'expires_at' => null,
                    'reason' => 'استبدال بهدية: ' . $gift->name,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $currentBalance -= $gift->points_required;
            }

            // Update wallet balance
            $wallet->update(['balance' => max(0, $currentBalance)]);
        }

        $this->command->info('Created point transactions for ' . $wallets->count() . ' users.');
    }
}