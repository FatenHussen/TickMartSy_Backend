<?php

namespace Database\Seeders;

use App\Models\PointExchange;
use App\Models\PointTransaction;
use App\Models\Coupon;
use App\Models\Gift;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PointExchangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $redeemedTransactions = PointTransaction::where('status', 'redeemed')
            ->with('user')
            ->get();

        if ($redeemedTransactions->isEmpty()) {
            $this->command->warn('No redeemed transactions found. Run PointTransactionSeeder first.');
            return;
        }

        $coupons = Coupon::where('is_active', true)->pluck('id')->toArray();
        $gifts = Gift::all();

        $exchangeTypes = ['coupon', 'free_delivery', 'gift'];
        $count = 0;
        $hasGiftExchange = false;

        // ترتيب: استبدال هدايا أولاً، ثم الباقي
        $giftTransactions = $redeemedTransactions->filter(fn($t) => $t->source === 'gift_exchange' || $t->reference_type === 'gift');
        $otherTransactions = $redeemedTransactions->filter(fn($t) => $t->source !== 'gift_exchange' && $t->reference_type !== 'gift');
        $orderedTransactions = $giftTransactions->merge($otherTransactions)->take(15);

        foreach ($orderedTransactions as $transaction) {
            // تجنب إنشاء أكثر من exchange لنفس الـ transaction
            if (PointExchange::where('transaction_id', $transaction->id)->exists()) {
                continue;
            }

            // استبدال هدية: إما معاملة gift_exchange أو نتأكد أن يكون واحد على الأقل هدية
            $type = ($transaction->source === 'gift_exchange' || $transaction->reference_type === 'gift')
                ? 'gift'
                : ($hasGiftExchange ? $exchangeTypes[array_rand($exchangeTypes)] : 'gift');
            if ($type === 'gift') {
                $hasGiftExchange = true;
            }
            $pointsUsed = abs($transaction->points);

            $exchangeData = match ($type) {
                'coupon' => [
                    'coupon_id' => !empty($coupons) ? $coupons[array_rand($coupons)] : null,
                    'discount_amount' => round($pointsUsed * 0.01, 2),
                    'points_used' => $pointsUsed,
                    'expires_at' => Carbon::now()->addDays(30)->toDateString(),
                ],
                'free_delivery' => [
                    'delivery_zones' => ['all'],
                    'points_used' => $pointsUsed,
                    'expires_at' => Carbon::now()->addDays(30)->toDateString(),
                    'usage_count' => 1,
                ],
                'gift' => (function () use ($transaction, $gifts, $pointsUsed) {
                    $gift = $transaction->reference_type === 'gift' && $transaction->reference_id
                        ? Gift::find($transaction->reference_id)
                        : $gifts->first();
                    return [
                        'gift_id' => $gift?->id ?? 1,
                        'gift_name' => $gift?->name ?? 'هدية',
                        'points_used' => $pointsUsed,
                        'delivery_address' => null,
                    ];
                })(),
            };

            $status = $type === 'gift' ? (rand(0, 1) ? 'pending' : 'completed') : 'completed';

            PointExchange::create([
                'user_id' => $transaction->user_id,
                'transaction_id' => $transaction->id,
                'exchange_type' => $type,
                'exchange_data' => $exchangeData,
                'status' => $status,
                'delivered_at' => $status === 'completed' && rand(0, 1) ? Carbon::now()->subDays(rand(1, 10)) : null,
                'notes' => rand(0, 1) ? 'تم الاستبدال بنجاح' : null,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ]);

            $count++;
        }

        $this->command->info("Created {$count} point exchanges.");
    }
}
