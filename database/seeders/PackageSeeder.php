<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => json_encode(['ar' => 'Basic', 'en' => 'Basic']),
                'price' => 29.99,
                'duration_days' => 30,
                'monthly_orders_limit' => 50,
                'free_delivery_count' => 5,
                'discount_percentage' => 5,
                'points_bonus' => 100,
                'is_active' => true,
            ],
            [
                'name' => json_encode(['ar' => 'Standard', 'en' => 'Standard']),
                'price' => 59.99,
                'duration_days' => 30,
                'monthly_orders_limit' => 150,
                'free_delivery_count' => 15,
                'discount_percentage' => 10,
                'points_bonus' => 250,
                'is_active' => true,
            ],
            [
                'name' => json_encode(['ar' => 'Premium', 'en' => 'Premium']),
                'price' => 99.99,
                'duration_days' => 30,
                'monthly_orders_limit' => 500,
                'free_delivery_count' => 50,
                'discount_percentage' => 20,
                'points_bonus' => 500,
                'is_active' => true,
            ],
            [
                'name' => json_encode(['ar' => 'Inactive Package', 'en' => 'Inactive Package']),
                'price' => 19.99,
                'duration_days' => 15,
                'monthly_orders_limit' => 20,
                'free_delivery_count' => 2,
                'discount_percentage' => 0,
                'points_bonus' => 50,
                'is_active' => false,
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}
