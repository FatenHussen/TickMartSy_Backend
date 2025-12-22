<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'name' => 'store1',
                'email' => 'store1@example.com',
                'owner_phone' => '01000000001',
                'password' => Hash::make('password'),
                'store_name' => ['en' => 'ElectroShop', 'ar' => 'إلكتروشوب'],
                'description' => ['en' => 'Best electronics', 'ar' => 'أفضل الإلكترونيات'],
                'store_address' => ['en' => '123 Main St', 'ar' => '123 شارع الرئيسي'],
                'area_id' => 1,
                'category_id' => 1,
                'status' => 'active',
            ],
        ];

        foreach ($stores as $store) {
            Store::create($store);
        }
    }
}
