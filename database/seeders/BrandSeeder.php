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
                    'ar' => 'شانيل',
                    'en' => 'Chanel',
                ],
                'image' => 'brands/chanel.png',
            ],
            [
                'name' => [
                    'ar' => 'ديور',
                    'en' => 'Dior',
                ],
                'image' => 'brands/dior.png',
            ],
            [
                'name' => [
                    'ar' => 'غوتشي',
                    'en' => 'Gucci',
                ],
                'image' => 'brands/gucci.png',
            ],
            [
                'name' => [
                    'ar' => 'إيف سان لوران',
                    'en' => 'Yves Saint Laurent',
                ],
                'image' => 'brands/ysl.png',
            ],
            [
                'name' => [
                    'ar' => 'توم فورد',
                    'en' => 'Tom Ford',
                ],
                'image' => 'brands/tom-ford.png',
            ],
            [
                'name' => [
                    'ar' => 'فيرساتشي',
                    'en' => 'Versace',
                ],
                'image' => 'brands/versace.png',
            ],
            [
                'name' => [
                    'ar' => 'باكو رابان',
                    'en' => 'Paco Rabanne',
                ],
                'image' => 'brands/paco-rabanne.png',
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
