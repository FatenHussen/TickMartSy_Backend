<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'name' => [
                    'ar' => 'نايك',
                    'en' => 'Nike',
                ],
                'image' => 'brands/image2.jpg',
            ],
            [
                'name' => [
                    'ar' => 'أديداس',
                    'en' => 'Adidas',
                ],
                'image' => 'brands/image3.jpg',
            ],
            [
                'name' => [
                    'ar' => 'آبل',
                    'en' => 'Apple',
                ],
                'image' => 'brands/image4.png',
            ],
            [
                'name' => [
                    'ar' => 'سامسونغ',
                    'en' => 'Samsung',
                ],
                'image' => 'brands/image5.png',
            ],
            [
                'name' => [
                    'ar' => 'لوريال',
                    'en' => "L'Oréal",
                ],
                'image' => 'brands/image7.png',
            ],
            [
                'name' => [
                    'ar' => 'زارا',
                    'en' => 'Zara',
                ],
                'image' => 'brands/image6.png',
            ],
            [
                'name' => [
                    'ar' => 'شيغلام',
                    'en' => 'SHEGLAM',
                ],
                'image' => 'brands/image1.jpg',
            ],
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insert([
                'name' => json_encode($brand['name'], JSON_UNESCAPED_UNICODE),
                'image' => $brand['image'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
