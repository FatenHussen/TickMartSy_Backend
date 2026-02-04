<?php

namespace Database\Seeders;

use App\Models\Gift;
use Illuminate\Database\Seeder;

class GiftSeeder extends Seeder
{
    public function run(): void
    {
        $gifts = [
            [
                'name' => 'كوب قهوة مخصص',
                'description' => 'كوب قهوة عليه شعار التطبيق مع إمكانية إضافة اسمك',
                'image' => 'gifts/coffee-mug.jpg',
                'points_required' => 500,
                'stock_quantity' => 50,
                'category_id' => null,
                'terms_conditions' => 'يتم التسليم خلال 7-10 أيام عمل',
            ],
            [
                'name' => 'تيشيرت التطبيق',
                'description' => 'تيشيرت قطني عالي الجودة بتصميم التطبيق',
                'image' => 'gifts/t-shirt.jpg',
                'points_required' => 800,
                'stock_quantity' => 30,
                'category_id' => 1,
                'terms_conditions' => 'متوفر بجميع المقاسات. يتم التسليم خلال 5-7 أيام عمل',
            ],
            [
                'name' => 'سماعات بلوتوث',
                'description' => 'سماعات بلوتوث لاسلكية عالية الجودة',
                'image' => 'gifts/bluetooth-headphones.jpg',
                'points_required' => 1500,
                'stock_quantity' => 20,
                'category_id' => 3,
                'terms_conditions' => 'ضمان سنة واحدة. يتم التسليم خلال 3-5 أيام عمل',
            ],
            [
                'name' => 'كتاب الطبخ المميز',
                'description' => 'كتاب وصفات طبخ من أشهر الطهاة',
                'image' => 'gifts/cookbook.jpg',
                'points_required' => 600,
                'stock_quantity' => 25,
                'category_id' => 1,
                'terms_conditions' => 'كتاب مطبوع بجودة عالية. يتم التسليم خلال 2-3 أيام عمل',
            ],
            [
                'name' => 'باور بانك محمول',
                'description' => 'شاحن محمول بقوة 10000 مللي أمبير',
                'image' => 'gifts/power-bank.jpg',
                'points_required' => 1200,
                'stock_quantity' => 15,
                'category_id' => 1,
                'terms_conditions' => 'ضمان 6 أشهر. يتم التسليم خلال 3-5 أيام عمل',
            ],
            [
                'name' => 'قسيمة شراء 50$',
                'description' => 'قسيمة شراء بقيمة 50 دولار للاستخدام في التطبيق',
                'image' => 'gifts/voucher-50.jpg',
                'points_required' => 2000,
                'stock_quantity' => null, // Unlimited
                'category_id' => 1,
                'terms_conditions' => 'صالحة لمدة 6 أشهر من تاريخ الإصدار',
            ],
            [
                'name' => 'قسيمة شراء 100$',
                'description' => 'قسيمة شراء بقيمة 100 دولار للاستخدام في التطبيق',
                'image' => 'gifts/voucher-100.jpg',
                'points_required' => 3500,
                'stock_quantity' => null, // Unlimited
                'category_id' => 1,
                'terms_conditions' => 'صالحة لمدة 6 أشهر من تاريخ الإصدار',
            ],
        ];

        foreach ($gifts as $gift) {
            Gift::create($gift);
        }

        $this->command->info('Created ' . count($gifts) . ' gifts.');
    }
}