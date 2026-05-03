<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\QuickAction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuickActionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $actions = [
            [
                'slug' => 'home',
                'title' => ['en' => 'Home', 'ar' => 'الرئيسية'],
                'button_text' => ['en' => 'Open', 'ar' => 'فتح'],
                'order' => 1,
            ],
            [
                'slug' => 'products',
                'title' => ['en' => 'Products', 'ar' => 'المنتجات'],
                'button_text' => ['en' => 'Open', 'ar' => 'فتح'],
                'order' => 2,
            ],
            [
                'slug' => 'shops',
                'title' => ['en' => 'Shops', 'ar' => 'المتاجر'],
                'button_text' => ['en' => 'Open', 'ar' => 'فتح'],
                'order' => 3,
            ],
            [
                'slug' => 'baskets',
                'title' => ['en' => 'Baskets', 'ar' => 'السلال'],
                'button_text' => ['en' => 'Open', 'ar' => 'فتح'],
                'order' => 4,
            ],
            [
                'slug' => 'recipes',
                'title' => ['en' => 'Recipes', 'ar' => 'الوصفات'],
                'button_text' => ['en' => 'Open', 'ar' => 'فتح'],
                'order' => 5,
            ],
        ];

        foreach ($actions as $data) {
            $page = Page::where('slug', $data['slug'])->first();

            if (!$page) {
                $this->command->warn("QuickActionSeeder: skipping missing page slug {$data['slug']}");
                continue;
            }

            QuickAction::updateOrCreate(
                ['page_id' => $page->id],
                [
                    'title' => $data['title'],
                    'button_text' => $data['button_text'],
                    'icon' => 'icons/icon.png',
                    'order' => $data['order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
