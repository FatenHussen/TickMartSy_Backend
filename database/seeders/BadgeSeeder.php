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
                ],
                'type' => 'gif',
                'image'  => 'badge/bage1.gif',
            ],
            [
                'name' => [
                    'en' => 'Featured',
                    'ar' => 'مميز',

                ],
                'color' => 'warning',
                'type' => 'text',

            ],
            [
                'name' => [
                    'en' => 'Sale',
                    'ar' => 'خصم',
                ],
                'color' => 'danger',
                'type' => 'text',

            ],
            [
                'name' => [],
                'type' => 'gif',
                'image' => 'badge/badge2.gif'

            ],
        ];

        $badgeModels = collect($badges)->map(fn($badge) => Badge::create($badge));


    }
}
