<?php

namespace Database\Seeders;

use App\Enums\CartType;
use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Enums\OrderStatus;
use App\Models\AffiliateWalletTransaction;
use App\Models\Area;
use App\Models\Basket;
use App\Models\Brand;
use App\Models\Complaint;
use App\Models\Driver;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Recipe;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;

class LeenUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'leen@gmail.com')->first();

        if (!$user) {
            $this->command->warn('Leen user not found. Run UserSeeder first.');
            return;
        }

        $areaId = $user->area_id ?? Area::value('id');
        if (!$areaId) {
            $this->command->warn('No areas found. Skipping LeenUserSeeder.');
            return;
        }

        $this->ensureAffiliateProfile($user, $areaId);

        $homeAddress = $this->ensureAddresses($user, $areaId);

        $orders = $this->ensureOrders($user, $homeAddress);

        $this->seedFavorites($user);
        $this->seedRatings($user, $orders);
        $this->seedComplaints($user, $orders);

        $this->command->info('Leen user seeded with orders, addresses, favorites, ratings, complaints, and affiliate data.');
    }

    private function ensureAffiliateProfile(User $user, int $areaId): void
    {
        $user->forceFill([
            'area_id' => $areaId,
            'phone_verified_at' => $user->phone_verified_at ?? now(),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'is_affiliate' => true,
            'affiliate_approved' => true,
            'affiliate_id' => $user->affiliate_id ?: 'LEEN67890',
            'affiliate_rate' => $user->affiliate_rate ?: 25,
            'affiliate_visits' => max($user->affiliate_visits ?? 0, 42),
        ])->save();
    }

    private function ensureAddresses(User $user, int $areaId): UserAddress
    {
        $home = UserAddress::firstOrCreate(
            ['user_id' => $user->id, 'label' => 'Home'],
            [
                'area_id' => $areaId,
                'street_name' => 'Al Malki Street',
                'nearest_landmark' => 'Near City Mall',
                'building_number' => '12B',
                'floor_apartment' => '3rd Floor - Apt 8',
                'contact_phone' => $user->phone ?? '0991234567',
                'lat' => 33.507555,
                'lng' => 36.264233,
                'is_default' => true,
            ]
        );

        UserAddress::firstOrCreate(
            ['user_id' => $user->id, 'label' => 'Work'],
            [
                'area_id' => $areaId,
                'street_name' => 'Hamra Avenue',
                'nearest_landmark' => 'Opposite ABC Bank',
                'building_number' => '45',
                'floor_apartment' => '5th Floor',
                'contact_phone' => $user->phone ?? '0991234567',
                'lat' => 33.513807,
                'lng' => 36.276528,
                'is_default' => false,
            ]
        );

        if (!$home->is_default) {
            $home->forceFill(['is_default' => true])->save();
        }

        return $home;
    }

    private function ensureOrders(User $user, UserAddress $address): array
    {
        $existing = Order::where('user_id', $user->id)->get();
        if ($existing->count() >= 10) {
            return $existing->all();
        }

        $variants = ShopProductVariant::with('productVariant.product')->take(12)->get();
        if ($variants->isEmpty()) {
            $this->command->warn('No shop product variants found. Skipping Leen orders.');
            return $existing->all();
        }

        $driverId = Driver::value('id');

        $scheduledBasket = Basket::where('is_schedule', true)->first();
        $scheduledBasketScheduleId = $scheduledBasket?->schedules()?->value('id');

        $scenarios = [
            [
                'status' => OrderStatus::DELIVERED->value,
                'timestamps' => [
                    'pending_at' => now()->subDays(8),
                    'preparing_at' => now()->subDays(8)->addMinutes(25),
                    'out_delivery_at' => now()->subDays(8)->addMinutes(70),
                    'delivered_at' => now()->subDays(8)->addMinutes(110),
                ],
                'driver_id' => $driverId,
                'is_instant' => false,
                'cart_type' => CartType::DEFAULT->value,
            ],
            [
                'status' => OrderStatus::PENDING->value,
                'timestamps' => [
                    'pending_at' => now()->subHours(2),
                ],
                'driver_id' => null,
                'is_instant' => true,
                'cart_type' => CartType::DEFAULT->value,
            ],
            [
                'status' => OrderStatus::PREPARING->value,
                'timestamps' => [
                    'pending_at' => now()->subHours(6),
                    'preparing_at' => now()->subHours(5)->addMinutes(10),
                ],
                'driver_id' => null,
                'is_instant' => true,
                'cart_type' => CartType::DEFAULT->value,
            ],
            [
                'status' => OrderStatus::PREPARING->value,
                'timestamps' => [
                    'pending_at' => now()->subHours(10),
                    'preparing_at' => now()->subHours(9)->addMinutes(15),
                    'out_delivery_at' => now()->subHours(8)->addMinutes(40),
                ],
                'driver_id' => $driverId,
                'is_instant' => false,
                'cart_type' => CartType::DEFAULT->value,
            ],
            [
                'status' => OrderStatus::DELIVERED->value,
                'timestamps' => [
                    'pending_at' => now()->subDays(2),
                    'preparing_at' => now()->subDays(2)->addMinutes(15),
                    'out_delivery_at' => now()->subDays(2)->addMinutes(50),
                    'delivered_at' => now()->subDays(2)->addMinutes(75),
                ],
                'driver_id' => $driverId,
                'is_instant' => true,
                'cart_type' => CartType::DEFAULT->value,
            ],
            [
                'status' => OrderStatus::CANCELLED->value,
                'timestamps' => [
                    'pending_at' => now()->subDays(1)->subHours(3),
                ],
                'driver_id' => null,
                'is_instant' => false,
                'cart_type' => CartType::DEFAULT->value,
            ],
            [
                'status' => OrderStatus::DELIVERED->value,
                'timestamps' => [
                    'pending_at' => now()->subDays(12),
                    'preparing_at' => now()->subDays(12)->addMinutes(30),
                    'out_delivery_at' => now()->subDays(12)->addMinutes(80),
                    'delivered_at' => now()->subDays(12)->addMinutes(120),
                ],
                'driver_id' => $driverId,
                'is_instant' => false,
                'cart_type' => CartType::ADMIN_CART->value,
            ],
            [
                'status' => OrderStatus::DELIVERED->value,
                'timestamps' => [
                    'pending_at' => now()->subDays(6),
                    'preparing_at' => now()->subDays(6)->addMinutes(20),
                    'out_delivery_at' => now()->subDays(6)->addMinutes(55),
                    'delivered_at' => now()->subDays(6)->addMinutes(95),
                ],
                'driver_id' => $driverId,
                'is_instant' => false,
                'cart_type' => CartType::SCHEDULE_ADMIN_CART->value,
                'basket_id' => $scheduledBasket?->id,
                'basket_schedule_id' => $scheduledBasketScheduleId,
            ],
            [
                'status' => OrderStatus::PENDING->value,
                'timestamps' => [
                    'pending_at' => now()->subHours(12),
                ],
                'driver_id' => null,
                'is_instant' => false,
                'cart_type' => CartType::RECIPE->value,
            ],
            [
                'status' => OrderStatus::DELIVERED->value,
                'timestamps' => [
                    'pending_at' => now()->subDays(18),
                    'preparing_at' => now()->subDays(18)->addMinutes(25),
                    'out_delivery_at' => now()->subDays(18)->addMinutes(70),
                    'delivered_at' => now()->subDays(18)->addMinutes(105),
                ],
                'driver_id' => $driverId,
                'is_instant' => false,
                'cart_type' => CartType::DEFAULT->value,
            ],
        ];

        $orders = $existing->all();
        $startIndex = $existing->count();
        $targetCount = 10;
        $variantIndex = 0;

        for ($i = $startIndex; $i < $targetCount; $i++) {
            $scenario = $scenarios[$i % count($scenarios)];
            $itemVariants = $variants->slice($variantIndex, 2);
            if ($itemVariants->isEmpty()) {
                $variantIndex = 0;
                $itemVariants = $variants->slice($variantIndex, 2);
            }
            $variantIndex += 2;

            $cartType = $scenario['cart_type'];
            $basketId = $scenario['basket_id'] ?? null;
            $basketScheduleId = $scenario['basket_schedule_id'] ?? null;
            if (
                $cartType === CartType::SCHEDULE_ADMIN_CART->value &&
                (!$basketId || !$basketScheduleId)
            ) {
                $cartType = CartType::DEFAULT->value;
                $basketId = null;
                $basketScheduleId = null;
            }

            $orders[] = $this->createOrder(
                $user,
                $address,
                $itemVariants,
                $scenario['status'],
                $scenario['timestamps'],
                $scenario['driver_id'],
                $scenario['is_instant'],
                $cartType,
                $basketId,
                $basketScheduleId
            );
        }

        return $orders;
    }

    private function createOrder(
        User $user,
        UserAddress $address,
        $variants,
        string $status,
        array $timestamps,
        ?int $driverId,
        bool $isInstant,
        string $cartType,
        ?int $basketId,
        ?int $basketScheduleId
    ): Order {
        $totalQty = 0;
        $subtotal = 0;
        $items = [];

        foreach ($variants as $variant) {
            $qty = rand(1, 2);
            $price = (int) ($variant->price ?? 10000);
            $totalQty += $qty;
            $subtotal += $price * $qty;
            $items[] = [
                'variant' => $variant,
                'quantity' => $qty,
                'price' => $price,
            ];
        }

        $delivery = 5000;

        $order = Order::create([
            'user_id' => $user->id,
            'user_address_id' => $address->id,
            'driver_id' => $driverId,
            'is_instant_delivery' => $isInstant,
            'status' => $status,
            'cart_type' => $cartType,
            'basket_id' => $basketId,
            'basket_schedule_id' => $basketScheduleId,
            'total_quantity' => $totalQty,
            'delivery_price' => $delivery,
            'subtotal' => $subtotal,
            'basket_discount' => 0,
            'total' => $subtotal + $delivery,
        ]);

        $order->forceFill([
            'affiliate_id' => $user->affiliate_id,
            'affiliate_rate' => $user->affiliate_rate,
            'affiliate_source' => 'link',
            'assigned_by' => $driverId ? 'admin' : null,
            'pending_at' => $timestamps['pending_at'] ?? null,
            'preparing_at' => $timestamps['preparing_at'] ?? null,
            'out_delivery_at' => $timestamps['out_delivery_at'] ?? null,
            'delivered_at' => $timestamps['delivered_at'] ?? null,
        ])->save();

        $order->refresh(); // مهم

        if (!$order->order_code) {
            $order->order_code = 'ORD-' .
                now()->format('ymd') . '-' .
                str_pad($order->id, 5, '0', STR_PAD_LEFT);

            $order->save();
        }

        foreach ($items as $item) {
            $variant = $item['variant'];
            OrderItem::create([
                'order_id' => $order->id,
                'shop_product_variant_id' => $variant->id,
                'product_name' => $variant->productVariant?->product?->name ?? 'Product',
                'variant_attributes' => [
                    'size' => 'M',
                ],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount' => 0,
                'item_status' => $status,
                'pending_at' => $timestamps['pending_at'] ?? null,
                'preparing_at' => $timestamps['preparing_at'] ?? null,
                'out_delivery_at' => $timestamps['out_delivery_at'] ?? null,
                'delivered_at' => $timestamps['delivered_at'] ?? null,
            ]);
        }

        if ($user->affiliate_id && $user->affiliate_rate) {
            AffiliateWalletTransaction::create([
                'affiliate_id' => $user->affiliate_id,
                'type' => 'commission',
                'amount' => round($order->total * ($user->affiliate_rate / 100), 2),
                'order_id' => $order->id,
            ]);
        }

        return $order;
    }

    private function seedFavorites(User $user): void
    {
        $favorites = [
            Product::class => Product::take(2)->get(),
            Basket::class => Basket::take(1)->get(),
            Recipe::class => Recipe::take(1)->get(),
            Shop::class => Shop::take(1)->get(),
            Brand::class => Brand::take(1)->get(),
        ];

        foreach ($favorites as $type => $items) {
            foreach ($items as $item) {
                Favorite::firstOrCreate([
                    'user_id' => $user->id,
                    'favoriteable_type' => $type,
                    'favoriteable_id' => $item->id,
                ]);
            }
        }
    }

    private function seedRatings(User $user, array $orders): void
    {
        $productId = Product::value('id');
        $shopId = Shop::value('id');
        $driverId = Driver::value('id');

        $orderId = null;
        if (!empty($orders)) {
            $orderId = $orders[0]->id ?? null;
        }

        if ($productId) {
            Rating::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'rateable_type' => Product::class,
                    'rateable_id' => $productId,
                    'order_id' => $orderId,
                ],
                [
                    'rating' => 5,
                    'comment' => 'Great quality and fresh.',
                    'is_verified' => true,
                ]
            );
        }

        if ($shopId) {
            Rating::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'rateable_type' => Shop::class,
                    'rateable_id' => $shopId,
                    'order_id' => null,
                ],
                [
                    'rating' => 4,
                    'comment' => 'Fast service and helpful staff.',
                    'is_verified' => true,
                ]
            );
        }

        if ($driverId) {
            Rating::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'rateable_type' => Driver::class,
                    'rateable_id' => $driverId,
                    'order_id' => $orderId,
                ],
                [
                    'rating' => 5,
                    'comment' => 'Delivered on time.',
                    'is_verified' => true,
                ]
            );
        }
    }

    private function seedComplaints(User $user, array $orders): void
    {
        if (count($orders) < 1) {
            return;
        }

        $first = $orders[0];
        $second = $orders[1] ?? $orders[0];
        $third = $orders[2] ?? $orders[0];

        Complaint::firstOrCreate(
            [
                'user_id' => $user->id,
                'order_id' => $first->id,
                'message' => 'Delivery was late and packaging was damaged.',
            ],
            [
                'type' => ComplaintType::ORDER->value,
                'status' => ComplaintStatus::NEW->value,
                'admin_response' => null,
                'images' => null,
            ]
        );

        Complaint::firstOrCreate(
            [
                'user_id' => $user->id,
                'order_id' => $second->id,
                'message' => 'Received wrong item in the order.',
            ],
            [
                'type' => ComplaintType::PRODUCT->value,
                'status' => ComplaintStatus::RESOLVED->value,
                'admin_response' => 'Refund issued and a replacement will be sent.',
                'images' => null,
            ]
        );

        Complaint::firstOrCreate(
            [
                'user_id' => $user->id,
                'order_id' => $third->id,
                'message' => 'Driver was late without notice.',
            ],
            [
                'type' => ComplaintType::DRIVER->value,
                'status' => ComplaintStatus::REJECTED->value,
                'admin_response' => 'Delivery was within the stated time window.',
                'images' => null,
            ]
        );
    }
}
