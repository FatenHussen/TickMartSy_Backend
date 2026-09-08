<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Icon;

class IconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create icons
        $icons = [
            [
                'name'        => ['ar' => 'جديد', 'en' => 'New'],
                'image'       => 'icons/secure-delivery.png',
                'description' => ['ar' => 'منتج جديد', 'en' => 'New product'],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'عرض خاص', 'en' => 'Special Offer'],
                'image'       => 'icons/secure-delivery.png',
                'description' => ['ar' => 'عرض لفترة محدودة', 'en' => 'Limited time offer'],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'الأكثر مبيعاً', 'en' => 'Best Seller'],
                'image'       => 'icons/secure-delivery.png',
                'description' => ['ar' => 'من أكثر المنتجات مبيعاً', 'en' => 'One of the best selling products'],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'توصيل مجاني', 'en' => 'Free Delivery'],
                'image'       => 'icons/secure-delivery.png',
                'description' => ['ar' => 'توصيل مجاني للمنتج', 'en' => 'Free delivery for this product'],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'خصم', 'en' => 'Discount'],
                'image'       => 'icons/secure-delivery.png',
                'description' => ['ar' => 'منتج عليه خصم', 'en' => 'Product on discount'],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'محدود', 'en' => 'Limited'],
                'image'       => 'icons/secure-delivery.png',
                'description' => ['ar' => 'كمية محدودة', 'en' => 'Limited quantity'],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'عضوي', 'en' => 'Organic'],
                'image'       => 'icons/secure-delivery.png',
                'description' => ['ar' => 'منتج عضوي طبيعي', 'en' => 'Natural organic product'],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'مستورد', 'en' => 'Imported'],
                'image'       => 'icons/secure-delivery.png',
                'description' => ['ar' => 'منتج مستورد', 'en' => 'Imported product'],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'دفع عند الاستلام', 'en' => 'Cash on Delivery'],
                'image'       => 'icons/cod.png',
                'description' => [
                    'ar' => 'تتيح لك طريقة الدفع عند الاستلام (COD) الدفع نقداً عند باب منزلك أو عملك مقابل المنتجات التي يتم تسليمها بواسطة تيك موول أو بواسطة البائع.',
                    'en' => 'Cash on Delivery (COD) allows you to pay in cash at your doorstep or workplace for products delivered by Tikmool or the seller.',
                ],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'منتج مكفول', 'en' => 'Warranted Product'],
                'image'       => 'icons/warranty.png',
                'description' => [
                    'ar' => 'تضمن تيك موول جودة منتجاتها المعروضة، بالإضافة إلى أن بعض المنتجات تشمل ضمان وكفالة لمدد مختلفة من قبل الجهة الموردة أو المصنعة، ويتم ذكر ذلك في بطاقة المنتج عند توفره. يخضع الضمان لسياسة الضمان المعتمدة لدينا. يمكنك الاتصال بالشركة المصنعة أو العلامة التجارية أو البائع لمزيد من التفاصيل في حال كان المنتج يحمل بطاقة ضمان.',
                    'en' => 'Tikmool guarantees the quality of its listed products. Some products also include a warranty for various durations from the supplier or manufacturer, which is mentioned on the product card when available. The warranty is subject to our approved warranty policy. You may contact the manufacturer, brand, or seller for more details if the product carries a warranty card.',
                ],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'توصيل آمن', 'en' => 'Secure Delivery'],
                'image'       => 'icons/secure-delivery.png',
                'description' => [
                    'ar' => 'عملية التوصيل مضمونة ونراعي إجراءات السلامة لنتأكد من ذلك.',
                    'en' => 'The delivery process is guaranteed and we follow safety procedures to ensure that.',
                ],
                'is_active'   => true,
            ],
            [
                'name'        => ['ar' => 'معاملتك آمنة', 'en' => 'Secure Transaction'],
                'image'       => 'icons/secure-transaction.png',
                'description' => [
                    'ar' => 'نعمل دائماً لحماية أمنك وخصوصيتك. يقوم نظام أمان الدفع الخاص بنا بتشفير معلوماتك أثناء النقل. ولا يتم مشاركة تفاصيل بطاقتك الائتمانية مع بائعين تابعين لجهات خارجية، ولا نبيع معلوماتك إلى جهات أخرى.',
                    'en' => 'We always work to protect your security and privacy. Our payment security system encrypts your information during transmission. Your credit card details are not shared with third-party sellers, and we do not sell your information to others.',
                ],
                'is_active'   => true,
            ],
        ];

        foreach ($icons as $iconData) {
            Icon::updateOrCreate(
                ['image' => $iconData['image']],
                $iconData
            );
        }

        $this->command->info('Icons created successfully!');
    }
}
