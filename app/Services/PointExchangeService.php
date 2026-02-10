<?php

namespace App\Services;

use App\Models\PointExchange;
use App\Models\Gift;
use App\Models\Coupon;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\DB;

class PointExchangeService
{
    public function __construct(
        private PointService $pointService
    ) {}

    /**
     * Get available exchange options for user
     */
    public function getExchangeOptions(int $userId): array
    {
        $userWallet = $this->pointService->getOrCreateWallet($userId);
        $settings = SettingsHelper::getExchangeSettings();
        
        $options = [];

        // Check if user has minimum points
        if ($userWallet->balance < $settings['min_points']) {
            return [
                'available' => false,
                'message' => "تحتاج إلى {$settings['min_points']} نقطة على الأقل للاستبدال",
                'current_balance' => $userWallet->balance,
                'min_required' => $settings['min_points'],
            ];
        }

        // Coupon exchange
        if ($settings['coupon_enabled']) {
            $options['coupon'] = [
                'enabled' => true,
                'min_points' => $settings['min_points'],
                'max_points' => min($userWallet->balance, $settings['max_points']),
                'discount_rate' => $settings['coupon_discount_rate'],
                'description' => 'استبدال النقاط بكوبون خصم',
            ];
        }

        // Free delivery exchange
        if ($settings['free_delivery_enabled']) {
            $options['free_delivery'] = [
                'enabled' => $userWallet->balance >= $settings['free_delivery_points'],
                'points_cost' => $settings['free_delivery_points'],
                'description' => 'توصيل مجاني للطلب القادم',
            ];
        }

        // Gifts exchange
        if ($settings['gifts_enabled']) {
            $availableGifts = Gift::available()
                ->where('points_required', '<=', $userWallet->balance)
                ->orderBy('points_required')
                ->get();

            $options['gifts'] = [
                'enabled' => $availableGifts->count() > 0,
                'available_gifts' => $availableGifts->map(function ($gift) {
                    return [
                        'id' => $gift->id,
                        'name' => $gift->name,
                        'description' => $gift->description,
                        'image' => $gift->image,
                        'points_required' => $gift->points_required,
                        'stock_quantity' => $gift->stock_quantity,
                        'category' => $gift->category,
                    ];
                }),
            ];
        }

        return [
            'available' => true,
            'current_balance' => $userWallet->balance,
            'options' => $options,
        ];
    }

    /**
     * Exchange points for coupon
     */
    public function exchangeForCoupon(int $userId, int $points, ?int $couponId = null): ?bool
    {
        $settings = SettingsHelper::getExchangeSettings();
        
        // Validate points
        if ($points < $settings['min_points'] || $points > $settings['max_points']) {
            return null;
        }

        return DB::transaction(function () use ($userId, $points, $couponId, $settings) {
            // Deduct points
            $transaction = $this->pointService->redeemPoints(
                $userId,
                $points,
                'استبدال بكوبون خصم',
                'coupon_exchange'
            );

            if (!$transaction) {
                return null;
            }

            // Calculate discount amount
            $discountAmount = $points * $settings['coupon_discount_rate'];

            // Create exchange record
            $exchange = PointExchange::create([
                'user_id' => $userId,
                'transaction_id' => $transaction->resource['id'],
                'exchange_type' => 'coupon',
                'exchange_data' => [
                    'coupon_id' => $couponId,
                    'discount_amount' => $discountAmount,
                    'points_used' => $points,
                    'expires_at' => now()->addDays(30)->toDateString(),
                ],
                'status' => 'completed',
            ]);
            return true;
            // return [
            //     'exchange' => $exchange,
            //     'transaction' => $transaction,
            //     'discount_amount' => $discountAmount,
            // ];
        });
    }

    /**
     * Exchange points for free delivery
     */
    public function exchangeForFreeDelivery(int $userId, ?array $deliveryZones = null): ?array
    {
        $settings = SettingsHelper::getExchangeSettings();
        $pointsCost = $settings['free_delivery_points'];

        return DB::transaction(function () use ($userId, $pointsCost, $deliveryZones) {
            // Deduct points
            $transaction = $this->pointService->redeemPoints(
                $userId,
                $pointsCost,
                'استبدال بتوصيل مجاني',
                'free_delivery_exchange'
            );

            if (!$transaction) {
                return null;
            }

            // Create exchange record
            $exchange = PointExchange::create([
                'user_id' => $userId,
                'transaction_id' => $transaction->resource['id'],
                'exchange_type' => 'free_delivery',
                'exchange_data' => [
                    'delivery_zones' => $deliveryZones ?? ['all'],
                    'points_used' => $pointsCost,
                    'expires_at' => now()->addDays(30)->toDateString(),
                    'usage_count' => 1,
                ],
                'status' => 'completed',
            ]);

            return [
                'exchange' => $exchange,
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * Exchange points for gift
     */
    public function exchangeForGift(int $userId, int $giftId, ?array $deliveryAddress = null): ?array
    {
        $gift = Gift::find($giftId);
        
        if (!$gift || !$gift->isAvailable()) {
            return null;
        }

        return DB::transaction(function () use ($userId, $gift, $deliveryAddress) {
            // Check and decrease stock
            if (!$gift->decreaseStock()) {
                return null;
            }

            // Deduct points
            $transaction = $this->pointService->redeemPoints(
                $userId,
                $gift->points_required,
                "استبدال بهدية: {$gift->name}",
                'gift_exchange',
                $gift->id
            );

            if (!$transaction) {
                // Restore stock if transaction failed
                $gift->increment('stock_quantity');
                return null;
            }

            // Create exchange record
            $exchange = PointExchange::create([
                'user_id' => $userId,
                'transaction_id' => $transaction->resource['id'],
                'exchange_type' => 'gift',
                'exchange_data' => [
                    'gift_id' => $gift->id,
                    'gift_name' => $gift->name,
                    'points_used' => $gift->points_required,
                    'delivery_address' => $deliveryAddress,
                ],
                'status' => 'pending', // Needs admin approval for delivery
            ]);

            return [
                'exchange' => $exchange,
                'transaction' => $transaction,
                'gift' => $gift,
            ];
        });
    }

    /**
     * Get user exchange history
     */
    public function getUserExchangeHistory(int $userId, int $perPage = 15): array
    {
        $exchanges = PointExchange::where('user_id', $userId)
            ->with(['transaction'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return [
            'exchanges' => $exchanges->items(),
            'pagination' => [
                'current_page' => $exchanges->currentPage(),
                'last_page' => $exchanges->lastPage(),
                'per_page' => $exchanges->perPage(),
                'total' => $exchanges->total(),
            ],
        ];
    }

    /**
     * Check if user has active free delivery
     */
    public function hasActiveFreeDelivery(int $userId): bool
    {
        return PointExchange::where('user_id', $userId)
            ->where('exchange_type', 'free_delivery')
            ->where('status', 'completed')
            ->where(function ($query) {
                $query->whereNull('exchange_data->expires_at')
                    ->orWhere('exchange_data->expires_at', '>=', now()->toDateString());
            })
            ->exists();
    }
}