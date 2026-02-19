<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;
use App\Models\Product;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => [
                    'en' => 'New',
                    'ar' => 'جديد',
                ],
                'color' => 'success',
            ],
            [
                'name' => [
                    'en' => 'Featured',
                    'ar' => 'مميز',
                ],
                'color' => 'warning',
            ],
            [
                'name' => [
                    'en' => 'Sale',
                    'ar' => 'خصم',
                ],
                'color' => 'danger',
            ],
        ];

        $badgeModels = collect($badges)->map(fn($badge) => Badge::create($badge));

        // $products = Product::take(5)->get();

        // foreach ($products as $index => $product) {
        //     $product->badges()->attach([
        //         $badgeModels[0]->id => ['position' => 'top'],
        //         $badgeModels[1]->id => ['position' => 'bottom'],
        //         $badgeModels[2]->id => ['position' => 'bottom'],
        //     ]);
        // }
    }
}
