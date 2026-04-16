<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\DriverWalletTransaction;
use App\Models\Order;
use Illuminate\Database\Seeder;

class DriverWalletTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::query()
            ->with('driver')
            ->whereNotNull('driver_id')
            ->where('status', OrderStatus::DELIVERED->value)
            ->get();

        $created = 0;

        foreach ($orders as $order) {
            $driver = $order->driver;
            if (! $driver) {
                continue;
            }

            // Use original delivery fee when available, otherwise fallback.
            $deliveryFee = (float) ($order->original_delivery_price ?? 0);
            if ($deliveryFee <= 0) {
                $deliveryFee = (float) $order->delivery_price;
            }
            if ($deliveryFee <= 0) {
                continue;
            }

            $ratePercent = (float) ($driver->rate_per_order ?? 0);
            if ($ratePercent <= 0) {
                continue;
            }

            $amount = round($deliveryFee * ($ratePercent / 100), 2);
            if ($amount <= 0) {
                continue;
            }

            $transaction = DriverWalletTransaction::firstOrCreate(
                [
                    'order_id' => $order->id,
                    'driver_id' => $driver->id,
                ],
                [
                    'type' => (float) $order->delivery_price > 0 ? 'paid_by_user' : 'paid_by_system',
                    'amount' => $amount,
                    'delivery_fee' => round($deliveryFee, 2),
                    'rate_percent' => round($ratePercent, 2),
                ]
            );

            if ($transaction->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command->info("Driver wallet transactions seeded: {$created}");
    }
}
