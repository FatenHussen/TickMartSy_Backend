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
                'name' => [
                    'ar' => 'كوب قهوة مخصص',
                    'en' => 'Custom Coffee Mug'
                ],
                'description' => [
                    'ar' => 'كوب قهوة عليه شعار التطبيق مع إمكانية إضافة اسمك',
                    'en' => 'Coffee mug with app logo and option to add your name'
                ],
                'image' => 'gifts/coffee-mug.jpg',
                'points_required' => 500,
                'stock_quantity' => 50,
                'category_id' => null,
                'terms_conditions' => [
                    'ar' => 'يتم التسليم خلال 7-10 أيام عمل',
                    'en' => 'Delivery within 7-10 business days'
                ],
            ],
            [
                'name' => [
                    'ar' => 'تيشيرت التطبيق',
                    'en' => 'App T-Shirt'
                ],
                'description' => [
                    'ar' => 'تيشيرت قطني عالي الجودة بتصميم التطبيق',
                    'en' => 'High quality cotton t-shirt with app design'
                ],
                'image' => 'gifts/t-shirt.jpg',
                'points_required' => 800,
                'stock_quantity' => 30,
                'category_id' => 1,
                'terms_conditions' => [
                    'ar' => 'متوفر بجميع المقاسات. يتم التسليم خلال 5-7 أيام عمل',
                    'en' => 'Available in all sizes. Delivery within 5-7 business days'
                ],
            ],
            [
                'name' => [
                    'ar' => 'سماعات بلوتوث',
                    'en' => 'Bluetooth Headphones'
                ],
                'description' => [
                    'ar' => 'سماعات بلوتوث لاسلكية عالية الجودة',
                    'en' => 'High quality wireless Bluetooth headphones'
                ],
                'image' => 'gifts/bluetooth-headphones.jpg',
                'points_required' => 1500,
                'stock_quantity' => 20,
                'category_id' => 3,
                'terms_conditions' => [
                    'ar' => 'ضمان سنة واحدة. يتم التسليم خلال 3-5 أيام عمل',
                    'en' => 'One year warranty. Delivery within 3-5 business days'
                ],
            ],
            [
                'name' => [
                    'ar' => 'كتاب الطبخ المميز',
                    'en' => 'Premium Cookbook'
                ],
                'description' => [
                    'ar' => 'كتاب وصفات طبخ من أشهر الطهاة',
                    'en' => 'Recipe book from famous chefs'
                ],
                'image' => 'gifts/cookbook.jpg',
                'points_required' => 600,
                'stock_quantity' => 25,
                'category_id' => 1,
                'terms_conditions' => [
                    'ar' => 'كتاب مطبوع بجودة عالية. يتم التسليم خلال 2-3 أيام عمل',
                    'en' => 'High quality printed book. Delivery within 2-3 business days'
                ],
            ],
            [
                'name' => [
                    'ar' => 'باور بانك محمول',
                    'en' => 'Portable Power Bank'
                ],
                'description' => [
                    'ar' => 'شاحن محمول بقوة 10000 مللي أمبير',
                    'en' => '10000mAh portable charger'
                ],
                'image' => 'gifts/power-bank.jpg',
                'points_required' => 1200,
                'stock_quantity' => 15,
                'category_id' => 1,
                'terms_conditions' => [
                    'ar' => 'ضمان 6 أشهر. يتم التسليم خلال 3-5 أيام عمل',
                    'en' => '6 months warranty. Delivery within 3-5 business days'
                ],
            ],
            [
                'name' => [
                    'ar' => 'قسيمة شراء 50$',
                    'en' => '$50 Shopping Voucher'
                ],
                'description' => [
                    'ar' => 'قسيمة شراء بقيمة 50 دولار للاستخدام في التطبيق',
                    'en' => '$50 shopping voucher to use in the app'
                ],
                'image' => 'gifts/voucher-50.jpg',
                'points_required' => 2000,
                'stock_quantity' => null, // Unlimited
                'category_id' => 1,
                'terms_conditions' => [
                    'ar' => 'صالحة لمدة 6 أشهر من تاريخ الإصدار',
                    'en' => 'Valid for 6 months from issue date'
                ],
            ],
            [
                'name' => [
                    'ar' => 'قسيمة شراء 100$',
                    'en' => '$100 Shopping Voucher'
                ],
                'description' => [
                    'ar' => 'قسيمة شراء بقيمة 100 دولار للاستخدام في التطبيق',
                    'en' => '$100 shopping voucher to use in the app'
                ],
                'image' => 'gifts/voucher-100.jpg',
                'points_required' => 3500,
                'stock_quantity' => null, // Unlimited
                'category_id' => 1,
                'terms_conditions' => [
                    'ar' => 'صالحة لمدة 6 أشهر من تاريخ الإصدار',
                    'en' => 'Valid for 6 months from issue date'
                ],
            ],
        ];

        foreach ($gifts as $gift) {
            Gift::create($gift);
        }

        $this->command->info('Created ' . count($gifts) . ' gifts.');
    }
}

