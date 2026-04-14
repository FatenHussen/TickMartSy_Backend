<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserBasketSchedule;
use App\Models\UserBasketScheduleItem;
use App\Models\Schedule;
use App\Models\Basket;
use App\Models\BasketSchedule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShopProductVariant;
use App\Models\UserAddress;
use App\Enums\CartType;
use App\Enums\OrderStatus;
use Illuminate\Database\Seeder;

class UserBasketsAndOrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Get first user
        $user = User::first();

        if (!$user) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        // Get user address or create one
        $address = UserAddress::where('user_id', $user->id)->first();
        if (!$address) {
            $address = UserAddress::create([
                'user_id' => $user->id,
                'area_id' => $user->area_id,
                'street' => 'شارع الجامعة',
                'building' => 'بناء 5',
                'floor' => '3',
                'apartment' => '12',
                'is_default' => true,
            ]);
        }

        // 1. Create User Scheduled Baskets (UserBasketSchedule)
        $this->createUserScheduledBaskets($user);

        // 2. Create Orders for Admin Baskets (subscription)
        $this->createSubscriptionOrders($user, $address);

        // 3. Create Orders for Admin Baskets (custom)
        $this->createCustomOrders($user, $address);

        $this->command->info('Created baskets and orders for user: ' . $user->name);
    }

    private function createUserScheduledBaskets($user)
    {
        $schedules = Schedule::take(2)->get();

        if ($schedules->isEmpty()) {
            $this->command->warn('No schedules found. Skipping user scheduled baskets.');
            return;
        }

        $variants = ShopProductVariant::with('productVariant.product')->take(10)->get();

        if ($variants->isEmpty()) {
            $this->command->warn('No product variants found. Skipping user scheduled baskets.');
            return;
        }

        foreach ($schedules as $index => $schedule) {
            $userBasket = UserBasketSchedule::create([
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'name' => 'سلتي المجدولة ' . ($index + 1),
                'is_active' => true,
                'start_date' => now()->addDays($index * 7),
            ]);

            // Add 3-5 items to basket
            $itemCount = rand(3, 5);
            foreach ($variants->random($itemCount) as $variant) {
                UserBasketScheduleItem::create([
                    'user_basket_schedule_id' => $userBasket->id,
                    'product_id' => $variant->productVariant->product->id,
                    'shop_product_variant_id' => $variant->id,
                    'quantity' => rand(1, 3),
                ]);
            }
        }

        $this->command->info('Created ' . $schedules->count() . ' user scheduled baskets.');
    }

    private function createSubscriptionOrders($user, $address)
    {
        $baskets = Basket::where('is_schedule', true)->take(2)->get();

        if ($baskets->isEmpty()) {
            $this->command->warn('No scheduled baskets found. Skipping subscription orders.');
            return;
        }

        foreach ($baskets as $basket) {
            $basketSchedule = $basket->schedules()->first();

            if (!$basketSchedule) {
                continue;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'user_address_id' => $address->id,
                'basket_id' => $basket->id,
                'basket_schedule_id' => $basketSchedule->id,
                'cart_type' => CartType::SCHEDULE_ADMIN_CART->value,
                'status' => OrderStatus::DELIVERED->value,
                'subtotal' => $basket->calculated_price,
                'basket_discount' => $basket->discount_amount,
                'total' => $basket->final_price,
                'delivery_price' => $basket->delivery_price ?? 5,
                'total_quantity' => $basket->items->sum('quantity'),
                'delivered_at' => now()->subDays(rand(1, 10)),
            ]);

            $order->refresh(); // مهم

            if (!$order->order_code) {
                $order->order_code = 'ORD-' .
                    now()->format('ymd') . '-' .
                    str_pad($order->id, 5, '0', STR_PAD_LEFT);

                $order->save();
            }
            // Add order items from basket items
            foreach ($basket->items as $item) {
                $unitPrice = (float) $item->price;
                $finalPrice = $unitPrice;
                $subtotal = $finalPrice * (int) $item->quantity;
                $extrasTotal = 0;
                $total = $subtotal + $extrasTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'shop_product_variant_id' => $item->shop_product_variant_id,
                    'product_name' => $item->product->name ?? 'Product',
                    'quantity' => $item->quantity,
                    'price' => $unitPrice,
                    'unit_price' => $unitPrice,
                    'final_price' => $finalPrice,
                    'subtotal' => $subtotal,
                    'extras_total' => $extrasTotal,
                    'total' => $total,
                    'item_status' => OrderStatus::DELIVERED->value,
                ]);
            }
        }

        $this->command->info('Created ' . $baskets->count() . ' subscription orders.');
    }

    private function createCustomOrders($user, $address)
    {
        $baskets = Basket::where('is_schedule', false)->take(2)->get();

        if ($baskets->isEmpty()) {
            $this->command->warn('No custom baskets found. Skipping custom orders.');
            return;
        }

        foreach ($baskets as $basket) {
            $order = Order::create([
                'user_id' => $user->id,
                'user_address_id' => $address->id,
                'basket_id' => $basket->id,
                'cart_type' => CartType::ADMIN_CART->value,
                'status' => OrderStatus::DELIVERED->value,
                'subtotal' => $basket->calculated_price,
                'basket_discount' => $basket->discount_amount,
                'total' => $basket->final_price,
                'delivery_price' => $basket->delivery_price ?? 5,
                'total_quantity' => $basket->items->sum('quantity'),
                'delivered_at' => now()->subDays(rand(1, 10)),
            ]);
            $order->refresh(); // مهم

            if (!$order->order_code) {
                $order->order_code = 'ORD-' .
                    now()->format('ymd') . '-' .
                    str_pad($order->id, 5, '0', STR_PAD_LEFT);

                $order->save();
            }

            // Add order items from basket items
            foreach ($basket->items as $item) {
                $unitPrice = (float) $item->price;
                $finalPrice = $unitPrice;
                $subtotal = $finalPrice * (int) $item->quantity;
                $extrasTotal = 0;
                $total = $subtotal + $extrasTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'shop_product_variant_id' => $item->shop_product_variant_id,
                    'product_name' => $item->product->name ?? 'Product',
                    'quantity' => $item->quantity,
                    'price' => $unitPrice,
                    'unit_price' => $unitPrice,
                    'final_price' => $finalPrice,
                    'subtotal' => $subtotal,
                    'extras_total' => $extrasTotal,
                    'total' => $total,
                    'item_status' => OrderStatus::DELIVERED->value,
                ]);
            }
        }

        $this->command->info('Created ' . $baskets->count() . ' custom orders.');
    }
}
