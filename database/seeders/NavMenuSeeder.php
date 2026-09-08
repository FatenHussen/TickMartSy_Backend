<?php

namespace Database\Seeders;

use App\Models\NavMenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NavMenuSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            [
                'title' => ['en' => 'Main Categories', 'ar' => 'الفئات الرئيسية'],
                'type' => 'route',
                'route_key' => 'categories',
                'order' => 1,
            ],
            [
                'title' => ['en' => 'Brands', 'ar' => 'الماركات'],
                'type' => 'route',
                'route_key' => 'brands',
                'order' => 2,
            ],
            [
                'title' => ['en' => 'All shops', 'ar' => 'كل المتاجر'],
                'type' => 'route',
                'route_key' => 'shops',
                'order' => 3,
            ],
            [
                'title' => ['en' => 'My baskets', 'ar' => 'سلالي'],
                'type' => 'route',
                'route_key' => 'baskets',
                'order' => 4,
            ],
            [
                'title' => ['en' => 'Points & rewards', 'ar' => 'النقاط والمكافآت'],
                'type' => 'route',
                'route_key' => 'points',
                'order' => 5,
            ],
            [
                'title' => ['en' => 'Help & support', 'ar' => 'المساعدة والدعم'],
                'type' => 'route',
                'route_key' => 'help',
                'order' => 6,
            ],
            [
                'title' => ['en' => 'Subscription packages', 'ar' => 'باقات الاشتراك'],
                'type' => 'route',
                'route_key' => 'subscriptions',
                'order' => 7,
            ],
        ];

        foreach ($items as $data) {
            NavMenuItem::updateOrCreate(
                ['type' => $data['type'], 'route_key' => $data['route_key']],
                [
                    'title' => $data['title'],
                    'order' => $data['order'],
                    'is_active' => true,
                    'open_in_new_tab' => false,
                ]
            );
        }
    }
}
