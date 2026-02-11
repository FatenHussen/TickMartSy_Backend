<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Basket;
use App\Models\BasketItem;

class BasketSeeder extends Seeder
{
    public function run(): void
    {
        $basket1 = Basket::create([
            'category_id'      => 1,
            'name'             => [
                'ar' => 'سلة أساسيات العائلة الأسبوعية',
                'en' => 'Family Weekly Essentials Basket',
            ],
            'num_varieties'    => 6,
            'offer_ends_at'    => null,
            'price'            => 0,
            'discount'         => 20,
            'discount_type'    => 'percentage',
            'rating'           => 4.8,
            'num_sold'         => 1247,
            'image'            => 'baskets/image.jpg',
            'delivery_price' => 20,
            'is_schedule' => 1

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
            'discount'         => 12.50,
            'discount_type'    => 'fixed',
            'rating'           => 4.5,
            'num_sold'         => 683,
            'image'            => 'baskets/image1.jpg',
            'delivery_price' => 20,
            'is_schedule' => 1


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
            'image'            => 'baskets/image2.jpg',
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
