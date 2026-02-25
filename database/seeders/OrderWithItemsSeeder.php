<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Enums\CartType;
use App\Models\AffiliateWalletTransaction;

class OrderWithItemsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $orders = [
            // 1️⃣ PENDING – instant
            [
                'order_code' => 1,
                'user_id' => 1,
                'user_address_id' => 1,
                'status' => OrderStatus::PENDING->value,
                'driver_id' => null,
                'is_instant_delivery' => true,
                'timestamps' => [
                    'pending_at' => $now,
                ],
            ],
            [
                'order_code' => 2,
                'user_id' => 2,
                'user_address_id' => 2,
                'status' => OrderStatus::PENDING->value,
                'driver_id' => null,
                'is_instant_delivery' => true,
                'timestamps' => [
                    'pending_at' => $now,
                ],
            ],
            [
                'order_code' => 3,
                'user_id' => 1,
                'user_address_id' => 1,
                'status' => OrderStatus::PENDING->value,
                'driver_id' => null,
                'is_instant_delivery' => true,
                'timestamps' => [
                    'pending_at' => $now,
                ],
            ],
            [
                'order_code' => 4,

                'user_id' => 2,
                'user_address_id' => 2,
                'status' => OrderStatus::PENDING->value,
                'driver_id' => null,
                'is_instant_delivery' => true,
                'timestamps' => [
                    'pending_at' => $now,
                ],
            ],

            // 2️⃣ PREPARING – non instant
            [
                'order_code' => 5,

                'user_id' => 2,
                'user_address_id' => 2,
                'status' => OrderStatus::PREPARING->value,
                'driver_id' => null,
                'is_instant_delivery' => true,
                'timestamps' => [
                    'pending_at' => $now->copy()->subMinutes(30),
                    'preparing_at' => $now->copy()->subMinutes(20),
                ],
            ],

            // 3️⃣ PREPARING – instant + driver
            [
                'order_code' => 6,

                'user_id' => 2,
                'user_address_id' => 2,
                'status' => OrderStatus::PREPARING->value,
                'driver_id' => 1,
                'is_instant_delivery' => false,
                'assigned_by' => 'admin',
                'timestamps' => [
                    'pending_at' => $now->copy()->subMinutes(40),
                    'preparing_at' => $now->copy()->subMinutes(25),
                ],
            ],

            // 4️⃣ OUT DELIVERY
            [
                'order_code' => 7,

                'user_id' => 2,
                'user_address_id' => 2,
                'status' => OrderStatus::OUT_DELIVERY->value,
                'driver_id' => 1,
                'is_instant_delivery' => false,
                'assigned_by' => 'admin',
                'timestamps' => [
                    'pending_at' => $now->copy()->subHour(),
                    'preparing_at' => $now->copy()->subMinutes(45),
                    'out_delivery_at' => $now->copy()->subMinutes(15),
                ],
            ],

            // 5️⃣ DELIVERED
            [
                'order_code' => 8,

                'user_id' => 2,
                'user_address_id' => 2,
                'status' => OrderStatus::DELIVERED->value,
                'driver_id' => 1,
                'is_instant_delivery' => false,
                'assigned_by' => 'admin',
                'timestamps' => [
                    'pending_at' => $now->copy()->subHours(2),
                    'preparing_at' => $now->copy()->subMinutes(90),
                    'out_delivery_at' => $now->copy()->subMinutes(45),
                    'delivered_at' => $now->copy()->subMinutes(10),
                ],
            ],
        ];

        foreach ($orders as $index => $data) {
            $order = Order::create(array_merge([

                'user_id' => $data['user_id'],
                'order_code' => $data['order_code'],
                'user_address_id' => $data['user_address_id'],
                'cart_type' => CartType::DEFAULT->value,
                'total_quantity' => 2,
                'delivery_price' => 5000,
                'subtotal' => 30000,
                'basket_discount' => 0,
                'total' => 35000,
                'assigned_by' => $data['assigned_by'] ?? null,
                'status' => $data['status'],
                'driver_id' => $data['driver_id'],
                'is_instant_delivery' => $data['is_instant_delivery'],
                'affiliate_id' => '12567',
                'affiliate_rate'   => 20,
                'affiliate_source' => 'link', // link | coupon | null

            ], $data['timestamps']));

            AffiliateWalletTransaction::create([
                'affiliate_id' => '12567',
                'type' => 'commission',
                'amount' => 500,
                'order_id' => $order->id,
            ]);

            // 🧾 items (2 لكل order)
            foreach ([1, 2] as $i) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'shop_product_variant_id' => $i,
                    'product_name' => "Product {$i}",
                    'variant_attributes' => [
                        'size' => 'medium',
                    ],
                    'quantity' => 1,
                    'price' => 15000,
                    'discount' => 0,

                    'item_status' => $data['status'],
                    ...$data['timestamps'],
                ]);
            }
        }
    }
}
