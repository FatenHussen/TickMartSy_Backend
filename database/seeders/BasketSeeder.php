<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Basket;
use App\Models\Schedule;

class BasketSeeder extends Seeder
{
    public function run(): void
    {
        $weekly = Schedule::query()->where('interval_days', 7)->first();
        $monthly = Schedule::query()->where('interval_days', 30)->first();

        $basket1 = Basket::create([
            'category_id'      => 1,
            'name'             => [
                'ar' => 'سلة أساسيات العائلة الأسبوعية',
                'en' => 'Family Weekly Essentials Basket',
            ],
            'num_varieties'    => 6,
            'offer_ends_at'    => null,
            'price'            => 0,
            'discount'         => 0,
            'discount_type'    => 'percentage',
            'has_custom_discount' => false,
            'rating'           => 4.8,
            'num_sold'         => 1247,
            'image'            => 'baskets/image.jpg',
            'delivery_price' => 20,
            'is_schedule' => 1,
            'schedule_id' => $weekly?->id,
        ]);


        $basket2 = Basket::create([
            'category_id'      => 2,
            'name'             =>  [
                'ar' => 'سلة أساسيات العائلة الأسبوعية',
                'en' => 'Family Weekly Essentials Basket',
            ],
            'num_varieties'    => 9,
            'offer_ends_at'    => null,
            'price'            => 0,
            'discount'         => 0,
            'discount_type'    => 'percentage',
            'has_custom_discount' => false,
            'rating'           => 4.5,
            'num_sold'         => 683,
            'image'            => 'baskets/image1.jpg',
            'delivery_price' => 20,
            'is_schedule' => 1,
            'schedule_id' => $monthly?->id,


        ]);

        $basket3 = Basket::create([
            'category_id'      => 3,
            'name'             =>  [
                'ar' => 'سلة أساسيات العائلة الأسبوعية',
                'en' => 'Family Weekly Essentials Basket',
            ],
            'num_varieties'    => 8,
            'offer_ends_at'    => '2026-04-10',
            'price'            => 0,
            'discount'         => 30,
            'discount_type'    => 'percentage',
            'rating'           => 4.9,
            'num_sold'         => 2156,
            'image'            => 'baskets/image3.jpg',
            'delivery_price' => 20,
            'is_schedule' => 0


        ]);

        $basket4 = Basket::create([
            'category_id'      => 1,
            'name'             =>   [
                'ar' => 'سلة أساسيات العائلة الأسبوعية',
                'en' => 'Family Weekly Essentials Basket',
            ],
            'num_varieties'    => 7,
            'offer_ends_at'    => '2026-03-15',
            'price'            => 0,
            'discount'         => 15,
            'discount_type'    => 'percentage',
            'rating'           => 4.6,
            'num_sold'         => 892,
            'image'            => 'baskets/image1.jpg',
            'delivery_price' => 20,
            'is_schedule' => 0

        ]);

    }
}
