<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductMedia;
use App\Models\ProductExtraDetail;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get product images
        $files = Storage::disk('public')->files('product');

        // Get categories
        $riceCategory = Category::where('name->en', 'Rice')->first();
        $bulgurCategory = Category::where('name->en', 'Bulgur')->first();
        $lentilsCategory = Category::where('name->en', 'Lentils')->first();
        $shortGrainRiceCategory = Category::where('name->en', 'Short Grain Rice')->first();
        $longGrainRiceCategory = Category::where('name->en', 'Long Grain Rice')->first();
        $electronicsCategory = Category::where('name->en', 'Electronics')->first();
        $fashionCategory = Category::where('name->en', 'Fashion')->first();

        // Get countries for origin and sale
        $countries = \App\Models\Country::all()->pluck('id', 'name.en')->toArray();
        $saleCountries = \App\Models\SaleCountry::all()->pluck('id', 'name.en')->toArray();

        $products = [
            // Rice Products
            [
                'category_id' => $riceCategory?->id ?? 1,
                'vendor_id' => 1,
                'brand_id' => 1,
                'name' => ['en' => 'Premium Basmati Rice', 'ar' => 'أرز بسمتي فاخر'],
                'description' => ['en' => 'Long grain aromatic basmati rice', 'ar' => 'أرز بسمتي طويل الحبة عطري'],
                'full_description' => [
                    'en' => 'Premium quality aged basmati rice with extra long grains. Perfect for biryani, pilaf, and everyday meals. Naturally aromatic with a delicate flavor.',
                    'ar' => 'أرز بسمتي فاخر معتق بحبات طويلة جداً. مثالي للبرياني والأرز بالخلطة والوجبات اليومية. عطري طبيعياً بنكهة رقيقة.'
                ],
                'sku' => 'RICE-BAS-001',
                'country_id' => $countries['India'] ?? null,
                'sale_country_id' => $saleCountries['Saudi Arabia'] ?? null,
                'model' => 'BASMATI-PREMIUM',
                'price' => 25,
                'cost_price' => 20,
                'discount' => 10,
                'discount_type' => 'percentage',
                'quantity' => 150,
                'unit' => 'kg',
                'barcode' => '8901234567890',
                'time_prepare' => '00:25:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 12,
                'thumbnail' => 'products/thumbnails/basmati.jpg',
                'seo_title' => ['en' => 'Premium Basmati Rice - Best Quality', 'ar' => 'أرز بسمتي فاخر - أفضل جودة'],
                'seo_description' => ['en' => 'Buy premium basmati rice online', 'ar' => 'اشتري أرز بسمتي فاخر أونلاين'],
                'seo_keywords' => ['en' => 'basmati, rice, premium, indian', 'ar' => 'بسمتي، أرز، فاخر، هندي'],
                'seo_image' => 'products/seo/basmati-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '5 kg', 'ar' => '5 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Grain Type', 'ar' => 'نوع الحبة'], 'value' => ['en' => 'Extra Long', 'ar' => 'طويلة جداً'], 'price' => 0],
                    ['key' => ['en' => 'Cooking Time', 'ar' => 'وقت الطبخ'], 'value' => ['en' => '20-25 min', 'ar' => '20-25 دقيقة'], 'price' => 0],
                ]
            ],
            [
                'category_id' => $riceCategory?->id ?? 1,
                'vendor_id' => 1,
                'brand_id' => 2,
                'name' => ['en' => 'Egyptian Short Grain Rice', 'ar' => 'أرز مصري قصير الحبة'],
                'description' => ['en' => 'High quality Egyptian short grain rice', 'ar' => 'أرز مصري قصير الحبة عالي الجودة'],
                'full_description' => [
                    'en' => 'Premium Egyptian short grain rice, perfect for traditional Middle Eastern dishes like Kabsa, Mandi, and stuffed vegetables. Absorbs flavors beautifully.',
                    'ar' => 'أرز مصري قصير الحبة فاخر، مثالي للأطباق الشرق أوسطية التقليدية مثل الكبسة والمندي والمحاشي. يمتص النكهات بشكل رائع.'
                ],
                'sku' => 'RICE-EGY-002',
                'country_id' => $countries['Egypt'] ?? null,
                'sale_country_id' => $saleCountries['UAE'] ?? null,
                'model' => 'EGYPTIAN-SHORT',
                'price' => 18,
                'cost_price' => 14,
                'discount' => 15,
                'discount_type' => 'percentage',
                'quantity' => 200,
                'unit' => 'kg',
                'barcode' => '8901234567891',
                'time_prepare' => '00:20:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 12,
                'thumbnail' => 'products/thumbnails/egyptian-rice.jpg',
                'seo_title' => ['en' => 'Egyptian Short Grain Rice', 'ar' => 'أرز مصري قصير الحبة'],
                'seo_description' => ['en' => 'Authentic Egyptian rice for traditional dishes', 'ar' => 'أرز مصري أصلي للأطباق التقليدية'],
                'seo_keywords' => ['en' => 'egyptian, rice, short grain, kabsa', 'ar' => 'مصري، أرز، قصير، كبسة'],
                'seo_image' => 'products/seo/egyptian-rice-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '5 kg', 'ar' => '5 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Grain Type', 'ar' => 'نوع الحبة'], 'value' => ['en' => 'Short', 'ar' => 'قصيرة'], 'price' => 0],
                    ['key' => ['en' => 'Best For', 'ar' => 'الأفضل لـ'], 'value' => ['en' => 'Kabsa & Mandi', 'ar' => 'كبسة ومندي'], 'price' => 0],
                ]
            ],
            [
                'category_id' => $riceCategory?->id ?? 1,
                'vendor_id' => 1,
                'brand_id' => 3,
                'name' => ['en' => 'Jasmine Rice', 'ar' => 'أرز ياسمين'],
                'description' => ['en' => 'Fragrant Thai jasmine rice', 'ar' => 'أرز ياسمين تايلندي عطري'],
                'full_description' => [
                    'en' => 'Authentic Thai jasmine rice with natural floral aroma. Soft, slightly sticky texture perfect for Asian cuisine. Cooks fluffy and tender.',
                    'ar' => 'أرز ياسمين تايلندي أصلي برائحة زهرية طبيعية. قوام ناعم ولزج قليلاً مثالي للمطبخ الآسيوي. ينضج هشاً وطرياً.'
                ],
                'sku' => 'RICE-JAS-003',
                'country_id' => $countries['Thailand'] ?? null,
                'sale_country_id' => $saleCountries['Kuwait'] ?? null,
                'model' => 'JASMINE-THAI',
                'price' => 22,
                'discount' => 0,
                'quantity' => 120,
                'barcode' => '8901234567892',
                'time_prepare' => '00:18:00',
                'bought_with' => [],
                'is_instant_delivery' => false,
                'approval_status' => 'approved',
                'warranty_period' => 12,
                'seo_title' => ['en' => 'Thai Jasmine Rice - Aromatic', 'ar' => 'أرز ياسمين تايلندي - عطري'],
                'seo_description' => ['en' => 'Premium Thai jasmine rice', 'ar' => 'أرز ياسمين تايلندي فاخر'],
                'seo_keywords' => ['en' => 'jasmine, thai, rice, aromatic', 'ar' => 'ياسمين، تايلندي، أرز، عطري'],
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '4.5 kg', 'ar' => '4.5 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Origin', 'ar' => 'المنشأ'], 'value' => ['en' => 'Thailand', 'ar' => 'تايلاند'], 'price' => 0],
                    ['key' => ['en' => 'Texture', 'ar' => 'القوام'], 'value' => ['en' => 'Soft & Sticky', 'ar' => 'ناعم ولزج'], 'price' => 0],
                ]
            ],

            // Short Grain Rice Products
            [
                'category_id' => $shortGrainRiceCategory?->id ?? $riceCategory?->id ?? 1,
                'vendor_id' => 1,
                'brand_id' => 2,
                'name' => ['en' => 'Calrose Short Grain Rice', 'ar' => 'أرز كالروز قصير الحبة'],
                'description' => ['en' => 'Premium Calrose short grain rice', 'ar' => 'أرز كالروز قصير الحبة فاخر'],
                'full_description' => [
                    'en' => 'Premium Calrose short grain rice with soft, sticky texture. Perfect for sushi, rice bowls, and Asian dishes. Absorbs flavors beautifully.',
                    'ar' => 'أرز كالروز قصير الحبة فاخر بقوام ناعم ولزج. مثالي للسوشي وأطباق الأرز والأطباق الآسيوية. يمتص النكهات بشكل رائع.'
                ],
                'sku' => 'RICE-CAL-009',
                'country_id' => $countries['USA'] ?? null,
                'sale_country_id' => $saleCountries['Qatar'] ?? null,
                'model' => 'CALROSE-SHORT',
                'price' => 19,
                'cost_price' => 15,
                'discount' => 8,
                'discount_type' => 'percentage',
                'quantity' => 130,
                'unit' => 'kg',
                'barcode' => '8901234567898',
                'time_prepare' => '00:18:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 12,
                'thumbnail' => 'products/thumbnails/calrose.jpg',
                'seo_title' => ['en' => 'Calrose Short Grain Rice', 'ar' => 'أرز كالروز قصير الحبة'],
                'seo_description' => ['en' => 'Premium short grain rice for sushi', 'ar' => 'أرز قصير الحبة فاخر للسوشي'],
                'seo_keywords' => ['en' => 'calrose, short grain, sushi, rice', 'ar' => 'كالروز، قصير، سوشي، أرز'],
                'seo_image' => 'products/seo/calrose-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '5 kg', 'ar' => '5 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Grain Type', 'ar' => 'نوع الحبة'], 'value' => ['en' => 'Short', 'ar' => 'قصيرة'], 'price' => 0],
                    ['key' => ['en' => 'Best For', 'ar' => 'الأفضل لـ'], 'value' => ['en' => 'Sushi & Rice Bowls', 'ar' => 'سوشي وأطباق الأرز'], 'price' => 0],
                ]
            ],
            [
                'category_id' => $shortGrainRiceCategory?->id ?? $riceCategory?->id ?? 1,
                'vendor_id' => 1,
                'brand_id' => 3,
                'name' => ['en' => 'Arborio Rice', 'ar' => 'أرز أربوريو'],
                'description' => ['en' => 'Italian Arborio rice for risotto', 'ar' => 'أرز أربوريو إيطالي للريزوتو'],
                'full_description' => [
                    'en' => 'Authentic Italian Arborio rice, the gold standard for risotto. High starch content creates creamy texture. Short, plump grains.',
                    'ar' => 'أرز أربوريو إيطالي أصلي، المعيار الذهبي للريزوتو. محتوى نشا عالي يخلق قواماً كريمياً. حبات قصيرة وممتلئة.'
                ],
                'sku' => 'RICE-ARB-010',
                'country_id' => $countries['Italy'] ?? null,
                'sale_country_id' => $saleCountries['Bahrain'] ?? null,
                'model' => 'ARBORIO-ITALIAN',
                'price' => 28,
                'cost_price' => 23,
                'discount' => 0,
                'discount_type' => 'percentage',
                'quantity' => 90,
                'unit' => 'kg',
                'barcode' => '8901234567899',
                'time_prepare' => '00:22:00',
                'bought_with' => [],
                'is_instant_delivery' => false,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 12,
                'thumbnail' => 'products/thumbnails/arborio.jpg',
                'seo_title' => ['en' => 'Arborio Rice - Italian Risotto', 'ar' => 'أرز أربوريو - ريزوتو إيطالي'],
                'seo_description' => ['en' => 'Authentic Italian Arborio rice', 'ar' => 'أرز أربوريو إيطالي أصلي'],
                'seo_keywords' => ['en' => 'arborio, risotto, italian, rice', 'ar' => 'أربوريو، ريزوتو، إيطالي، أرز'],
                'seo_image' => 'products/seo/arborio-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '1 kg', 'ar' => '1 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Grain Type', 'ar' => 'نوع الحبة'], 'value' => ['en' => 'Short & Plump', 'ar' => 'قصيرة وممتلئة'], 'price' => 0],
                    ['key' => ['en' => 'Best For', 'ar' => 'الأفضل لـ'], 'value' => ['en' => 'Risotto', 'ar' => 'ريزوتو'], 'price' => 0],
                ]
            ],

            // Long Grain Rice Products
            [
                'category_id' => $longGrainRiceCategory?->id ?? $riceCategory?->id ?? 1,
                'vendor_id' => 1,
                'brand_id' => 1,
                'name' => ['en' => 'Premium Long Grain White Rice', 'ar' => 'أرز أبيض طويل الحبة فاخر'],
                'description' => ['en' => 'Premium long grain white rice', 'ar' => 'أرز أبيض طويل الحبة فاخر'],
                'full_description' => [
                    'en' => 'Premium long grain white rice that cooks fluffy and separate. Perfect for everyday meals, pilafs, and side dishes. Versatile and reliable.',
                    'ar' => 'أرز أبيض طويل الحبة فاخر ينضج هشاً ومنفصلاً. مثالي للوجبات اليومية والأرز بالخلطة والأطباق الجانبية. متعدد الاستخدامات وموثوق.'
                ],
                'sku' => 'RICE-LNG-011',
                'country_id' => $countries['Pakistan'] ?? null,
                'sale_country_id' => $saleCountries['Oman'] ?? null,
                'model' => 'LONG-GRAIN-WHITE',
                'price' => 16,
                'cost_price' => 12,
                'discount' => 12,
                'discount_type' => 'percentage',
                'quantity' => 180,
                'unit' => 'kg',
                'barcode' => '8901234567900',
                'time_prepare' => '00:20:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 12,
                'thumbnail' => 'products/thumbnails/long-grain.jpg',
                'seo_title' => ['en' => 'Long Grain White Rice', 'ar' => 'أرز أبيض طويل الحبة'],
                'seo_description' => ['en' => 'Premium long grain rice for everyday cooking', 'ar' => 'أرز طويل الحبة فاخر للطبخ اليومي'],
                'seo_keywords' => ['en' => 'long grain, white rice, everyday', 'ar' => 'طويل الحبة، أرز أبيض، يومي'],
                'seo_image' => 'products/seo/long-grain-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '5 kg', 'ar' => '5 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Grain Type', 'ar' => 'نوع الحبة'], 'value' => ['en' => 'Long', 'ar' => 'طويلة'], 'price' => 0],
                    ['key' => ['en' => 'Texture', 'ar' => 'القوام'], 'value' => ['en' => 'Fluffy & Separate', 'ar' => 'هش ومنفصل'], 'price' => 0],
                ]
            ],
            [
                'category_id' => $longGrainRiceCategory?->id ?? $riceCategory?->id ?? 1,
                'vendor_id' => 1,
                'brand_id' => 2,
                'name' => ['en' => 'Brown Long Grain Rice', 'ar' => 'أرز بني طويل الحبة'],
                'description' => ['en' => 'Healthy brown long grain rice', 'ar' => 'أرز بني طويل الحبة صحي'],
                'full_description' => [
                    'en' => 'Nutritious brown long grain rice with bran intact. Rich in fiber, vitamins, and minerals. Nutty flavor and chewy texture. Takes longer to cook but worth it.',
                    'ar' => 'أرز بني طويل الحبة مغذي مع النخالة السليمة. غني بالألياف والفيتامينات والمعادن. نكهة جوزية وقوام مطاطي. يستغرق وقتاً أطول للطبخ لكنه يستحق.'
                ],
                'sku' => 'RICE-BRN-012',
                'country_id' => $countries['USA'] ?? null,
                'sale_country_id' => $saleCountries['Saudi Arabia'] ?? null,
                'model' => 'BROWN-LONG-GRAIN',
                'price' => 21,
                'cost_price' => 17,
                'discount' => 5,
                'discount_type' => 'percentage',
                'quantity' => 110,
                'unit' => 'kg',
                'barcode' => '8901234567901',
                'time_prepare' => '00:35:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 12,
                'thumbnail' => 'products/thumbnails/brown-rice.jpg',
                'seo_title' => ['en' => 'Brown Long Grain Rice - Healthy', 'ar' => 'أرز بني طويل الحبة - صحي'],
                'seo_description' => ['en' => 'Nutritious brown rice with fiber', 'ar' => 'أرز بني مغذي بالألياف'],
                'seo_keywords' => ['en' => 'brown rice, long grain, healthy, fiber', 'ar' => 'أرز بني، طويل الحبة، صحي، ألياف'],
                'seo_image' => 'products/seo/brown-rice-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '2 kg', 'ar' => '2 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Grain Type', 'ar' => 'نوع الحبة'], 'value' => ['en' => 'Long Brown', 'ar' => 'طويلة بنية'], 'price' => 0],
                    ['key' => ['en' => 'Fiber', 'ar' => 'الألياف'], 'value' => ['en' => 'High', 'ar' => 'عالية'], 'price' => 0],
                ]
            ],

            // Electronics Products
            [
                'category_id' => $electronicsCategory?->id ?? 1,
                'vendor_id' => 1,
                'brand_id' => 1,
                'name' => ['en' => 'Wireless Bluetooth Headphones', 'ar' => 'سماعات بلوتوث لاسلكية'],
                'description' => ['en' => 'Premium wireless headphones', 'ar' => 'سماعات لاسلكية فاخرة'],
                'full_description' => [
                    'en' => 'Premium wireless Bluetooth headphones with noise cancellation. 30-hour battery life, comfortable design, and superior sound quality.',
                    'ar' => 'سماعات بلوتوث لاسلكية فاخرة مع إلغاء الضوضاء. عمر بطارية 30 ساعة، تصميم مريح، وجودة صوت فائقة.'
                ],
                'sku' => 'ELEC-HEAD-013',
                'country_id' => $countries['China'] ?? null,
                'sale_country_id' => $saleCountries['UAE'] ?? null,
                'model' => 'BT-HEADPHONE-X1',
                'price' => 15,
                'cost_price' => 12,
                'discount' => 20,
                'discount_type' => 'percentage',
                'quantity' => 50,
                'unit' => 'piece',
                'barcode' => '8901234567902',
                'time_prepare' => '00:05:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 24,
                'thumbnail' => 'products/thumbnails/headphones.jpg',
                'seo_title' => ['en' => 'Wireless Bluetooth Headphones', 'ar' => 'سماعات بلوتوث لاسلكية'],
                'seo_description' => ['en' => 'Premium wireless headphones with noise cancellation', 'ar' => 'سماعات لاسلكية فاخرة مع إلغاء الضوضاء'],
                'seo_keywords' => ['en' => 'headphones, bluetooth, wireless, noise cancellation', 'ar' => 'سماعات، بلوتوث، لاسلكية، إلغاء ضوضاء'],
                'seo_image' => 'products/seo/headphones-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Battery Life', 'ar' => 'عمر البطارية'], 'value' => ['en' => '30 hours', 'ar' => '30 ساعة'], 'price' => 0],
                    ['key' => ['en' => 'Connectivity', 'ar' => 'الاتصال'], 'value' => ['en' => 'Bluetooth 5.0', 'ar' => 'بلوتوث 5.0'], 'price' => 0],
                    ['key' => ['en' => 'Features', 'ar' => 'المميزات'], 'value' => ['en' => 'Noise Cancellation', 'ar' => 'إلغاء الضوضاء'], 'price' => 0],
                ]
            ],
            [
                'category_id' => $electronicsCategory?->id ?? 1,
                'vendor_id' => 1,
                'brand_id' => 2,
                'name' => ['en' => 'Smart Watch Fitness Tracker', 'ar' => 'ساعة ذكية لتتبع اللياقة'],
                'description' => ['en' => 'Advanced fitness tracking smartwatch', 'ar' => 'ساعة ذكية متقدمة لتتبع اللياقة'],
                'full_description' => [
                    'en' => 'Advanced smartwatch with heart rate monitor, sleep tracking, GPS, and 50+ sport modes. Water resistant up to 50m. 7-day battery life.',
                    'ar' => 'ساعة ذكية متقدمة مع مراقب معدل ضربات القلب، تتبع النوم، GPS، وأكثر من 50 وضع رياضي. مقاومة للماء حتى 50م. عمر بطارية 7 أيام.'
                ],
                'sku' => 'ELEC-WATCH-014',
                'country_id' => $countries['China'] ?? null,
                'sale_country_id' => $saleCountries['Kuwait'] ?? null,
                'model' => 'SMARTWATCH-FIT-PRO',
                'price' => 85,
                'cost_price' => 65,
                'discount' => 15,
                'discount_type' => 'percentage',
                'quantity' => 75,
                'unit' => 'piece',
                'barcode' => '8901234567903',
                'time_prepare' => '00:05:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 12,
                'thumbnail' => 'products/thumbnails/smartwatch.jpg',
                'seo_title' => ['en' => 'Smart Watch Fitness Tracker', 'ar' => 'ساعة ذكية لتتبع اللياقة'],
                'seo_description' => ['en' => 'Advanced fitness tracking smartwatch', 'ar' => 'ساعة ذكية متقدمة لتتبع اللياقة'],
                'seo_keywords' => ['en' => 'smartwatch, fitness, tracker, heart rate', 'ar' => 'ساعة ذكية، لياقة، تتبع، معدل القلب'],
                'seo_image' => 'products/seo/smartwatch-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Battery Life', 'ar' => 'عمر البطارية'], 'value' => ['en' => '7 days', 'ar' => '7 أيام'], 'price' => 0],
                    ['key' => ['en' => 'Water Resistance', 'ar' => 'مقاومة الماء'], 'value' => ['en' => '50m', 'ar' => '50م'], 'price' => 0],
                    ['key' => ['en' => 'Sport Modes', 'ar' => 'الأوضاع الرياضية'], 'value' => ['en' => '50+', 'ar' => '+50'], 'price' => 0],
                ]
            ],

            // Fashion Products
            [
                'category_id' => $fashionCategory?->id ?? 2,
                'vendor_id' => 1,
                'brand_id' => 3,
                'name' => ['en' => 'Classic Cotton T-Shirt', 'ar' => 'تيشيرت قطني كلاسيكي'],
                'description' => ['en' => 'Premium cotton t-shirt', 'ar' => 'تيشيرت قطني فاخر'],
                'full_description' => [
                    'en' => 'Premium 100% cotton t-shirt with comfortable fit. Breathable fabric, durable stitching, and available in multiple colors. Perfect for everyday wear.',
                    'ar' => 'تيشيرت قطني 100% فاخر بقصة مريحة. قماش قابل للتنفس، خياطة متينة، ومتوفر بألوان متعددة. مثالي للارتداء اليومي.'
                ],
                'sku' => 'FASH-TSHIRT-015',
                'country_id' => $countries['Turkey'] ?? null,
                'sale_country_id' => $saleCountries['Qatar'] ?? null,
                'model' => 'COTTON-CLASSIC-TEE',
                'price' => 35,
                'cost_price' => 25,
                'discount' => 25,
                'discount_type' => 'percentage',
                'quantity' => 200,
                'unit' => 'piece',
                'barcode' => '8901234567904',
                'time_prepare' => '00:10:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 6,
                'thumbnail' => 'products/thumbnails/tshirt.jpg',
                'seo_title' => ['en' => 'Classic Cotton T-Shirt', 'ar' => 'تيشيرت قطني كلاسيكي'],
                'seo_description' => ['en' => 'Premium 100% cotton t-shirt', 'ar' => 'تيشيرت قطني 100% فاخر'],
                'seo_keywords' => ['en' => 'tshirt, cotton, classic, comfortable', 'ar' => 'تيشيرت، قطني، كلاسيكي، مريح'],
                'seo_image' => 'products/seo/tshirt-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Material', 'ar' => 'المادة'], 'value' => ['en' => '100% Cotton', 'ar' => '100% قطن'], 'price' => 0],
                    ['key' => ['en' => 'Fit', 'ar' => 'القصة'], 'value' => ['en' => 'Regular Fit', 'ar' => 'قصة عادية'], 'price' => 0],
                    ['key' => ['en' => 'Care', 'ar' => 'العناية'], 'value' => ['en' => 'Machine Washable', 'ar' => 'قابل للغسل بالغسالة'], 'price' => 0],
                ]
            ],
            [
                'category_id' => $fashionCategory?->id ?? 2,
                'vendor_id' => 1,
                'brand_id' => 1,
                'name' => ['en' => 'Denim Jeans - Slim Fit', 'ar' => 'جينز دينم - قصة ضيقة'],
                'description' => ['en' => 'Premium slim fit denim jeans', 'ar' => 'جينز دينم قصة ضيقة فاخر'],
                'full_description' => [
                    'en' => 'Premium denim jeans with slim fit design. Comfortable stretch fabric, classic 5-pocket style, and durable construction. Perfect for casual and smart-casual looks.',
                    'ar' => 'جينز دينم فاخر بتصميم قصة ضيقة. قماش مطاطي مريح، تصميم كلاسيكي بـ 5 جيوب، وبناء متين. مثالي للإطلالات الكاجوال والسمارت كاجوال.'
                ],
                'sku' => 'FASH-JEANS-016',
                'country_id' => $countries['Bangladesh'] ?? null,
                'sale_country_id' => $saleCountries['Bahrain'] ?? null,
                'model' => 'DENIM-SLIM-FIT',
                'price' => 75,
                'cost_price' => 55,
                'discount' => 30,
                'discount_type' => 'percentage',
                'quantity' => 150,
                'unit' => 'piece',
                'barcode' => '8901234567905',
                'time_prepare' => '00:10:00',
                'bought_with' => [],
                'is_instant_delivery' => false,
                'is_visible' => true,
                'approval_status' => 'approved',
                'rejection_reason' => null,
                'warranty_period' => 6,
                'thumbnail' => 'products/thumbnails/jeans.jpg',
                'seo_title' => ['en' => 'Denim Jeans - Slim Fit', 'ar' => 'جينز دينم - قصة ضيقة'],
                'seo_description' => ['en' => 'Premium slim fit denim jeans', 'ar' => 'جينز دينم قصة ضيقة فاخر'],
                'seo_keywords' => ['en' => 'jeans, denim, slim fit, casual', 'ar' => 'جينز، دينم، قصة ضيقة، كاجوال'],
                'seo_image' => 'products/seo/jeans-seo.jpg',
                'extra_details' => [
                    ['key' => ['en' => 'Material', 'ar' => 'المادة'], 'value' => ['en' => 'Stretch Denim', 'ar' => 'دينم مطاطي'], 'price' => 0],
                    ['key' => ['en' => 'Fit', 'ar' => 'القصة'], 'value' => ['en' => 'Slim Fit', 'ar' => 'قصة ضيقة'], 'price' => 0],
                    ['key' => ['en' => 'Style', 'ar' => 'الستايل'], 'value' => ['en' => '5-Pocket Classic', 'ar' => 'كلاسيكي 5 جيوب'], 'price' => 0],
                ]
            ],

            // Bulgur Products
            [
                'category_id' => $bulgurCategory?->id ?? 2,
                'vendor_id' => 1,
                'brand_id' => 1,
                'name' => ['en' => 'Fine Bulgur', 'ar' => 'برغل ناعم'],
                'description' => ['en' => 'Fine grade bulgur wheat', 'ar' => 'برغل قمح ناعم'],
                'full_description' => [
                    'en' => 'Premium fine bulgur wheat, perfect for tabbouleh, kibbeh, and salads. Pre-cooked and dried for quick preparation. Rich in fiber and nutrients.',
                    'ar' => 'برغل قمح ناعم فاخر، مثالي للتبولة والكبة والسلطات. مطبوخ مسبقاً ومجفف للتحضير السريع. غني بالألياف والعناصر الغذائية.'
                ],
                'sku' => 'BUL-FINE-004',
                'country_id' => $countries['Syria'] ?? null,
                'sale_country_id' => $saleCountries['Oman'] ?? null,
                'model' => 'BULGUR-FINE',
                'price' => 8,
                'discount' => 5,
                'quantity' => 180,
                'barcode' => '8901234567893',
                'time_prepare' => '00:10:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'approval_status' => 'approved',
                'warranty_period' => 18,
                'seo_title' => ['en' => 'Fine Bulgur Wheat', 'ar' => 'برغل ناعم'],
                'seo_description' => ['en' => 'Premium fine bulgur for tabbouleh', 'ar' => 'برغل ناعم فاخر للتبولة'],
                'seo_keywords' => ['en' => 'bulgur, fine, wheat, tabbouleh', 'ar' => 'برغل، ناعم، قمح، تبولة'],
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '1 kg', 'ar' => '1 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Grade', 'ar' => 'الدرجة'], 'value' => ['en' => 'Fine', 'ar' => 'ناعم'], 'price' => 0],
                    ['key' => ['en' => 'Preparation', 'ar' => 'التحضير'], 'value' => ['en' => 'Soak 10 min', 'ar' => 'نقع 10 دقائق'], 'price' => 0],
                ]
            ],
            [
                'category_id' => $bulgurCategory?->id ?? 2,
                'vendor_id' => 1,
                'brand_id' => 2,
                'name' => ['en' => 'Coarse Bulgur', 'ar' => 'برغل خشن'],
                'description' => ['en' => 'Coarse grade bulgur wheat', 'ar' => 'برغل قمح خشن'],
                'full_description' => [
                    'en' => 'Premium coarse bulgur wheat, ideal for pilafs, soups, and stuffing. Hearty texture with nutty flavor. Cooks in 15-20 minutes.',
                    'ar' => 'برغل قمح خشن فاخر، مثالي للبرغل بالخلطة والشوربات والحشوات. قوام قوي بنكهة جوزية. ينضج في 15-20 دقيقة.'
                ],
                'sku' => 'BUL-COAR-005',
                'country_id' => $countries['Turkey'] ?? null,
                'sale_country_id' => $saleCountries['Saudi Arabia'] ?? null,
                'model' => 'BULGUR-COARSE',
                'price' => 9,
                'discount' => 0,
                'quantity' => 160,
                'barcode' => '8901234567894',
                'time_prepare' => '00:20:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'approval_status' => 'approved',
                'warranty_period' => 18,
                'seo_title' => ['en' => 'Coarse Bulgur Wheat', 'ar' => 'برغل خشن'],
                'seo_description' => ['en' => 'Premium coarse bulgur for pilafs', 'ar' => 'برغل خشن فاخر للبرغل بالخلطة'],
                'seo_keywords' => ['en' => 'bulgur, coarse, wheat, pilaf', 'ar' => 'برغل، خشن، قمح، خلطة'],
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '1 kg', 'ar' => '1 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Grade', 'ar' => 'الدرجة'], 'value' => ['en' => 'Coarse', 'ar' => 'خشن'], 'price' => 0],
                    ['key' => ['en' => 'Cooking Time', 'ar' => 'وقت الطبخ'], 'value' => ['en' => '15-20 min', 'ar' => '15-20 دقيقة'], 'price' => 0],
                ]
            ],

            // Lentils Products
            [
                'category_id' => $lentilsCategory?->id ?? 3,
                'vendor_id' => 1,
                'brand_id' => 3,
                'name' => ['en' => 'Red Lentils', 'ar' => 'عدس أحمر'],
                'description' => ['en' => 'Premium red lentils', 'ar' => 'عدس أحمر فاخر'],
                'full_description' => [
                    'en' => 'High quality red lentils, perfect for soups, stews, and dal. Cooks quickly without soaking. Rich in protein and fiber. Creamy texture when cooked.',
                    'ar' => 'عدس أحمر عالي الجودة، مثالي للشوربات واليخنات والدال. ينضج بسرعة بدون نقع. غني بالبروتين والألياف. قوام كريمي عند الطبخ.'
                ],
                'sku' => 'LEN-RED-006',
                'country_id' => $countries['Canada'] ?? null,
                'sale_country_id' => $saleCountries['UAE'] ?? null,
                'model' => 'LENTILS-RED',
                'price' => 12,
                'discount' => 20,
                'quantity' => 140,
                'barcode' => '8901234567895',
                'time_prepare' => '00:15:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'approval_status' => 'approved',
                'warranty_period' => 24,
                'seo_title' => ['en' => 'Red Lentils - High Protein', 'ar' => 'عدس أحمر - غني بالبروتين'],
                'seo_description' => ['en' => 'Premium red lentils for soups', 'ar' => 'عدس أحمر فاخر للشوربات'],
                'seo_keywords' => ['en' => 'lentils, red, protein, soup', 'ar' => 'عدس، أحمر، بروتين، شوربة'],
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '1 kg', 'ar' => '1 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Type', 'ar' => 'النوع'], 'value' => ['en' => 'Split Red', 'ar' => 'أحمر مقشور'], 'price' => 0],
                    ['key' => ['en' => 'Protein', 'ar' => 'البروتين'], 'value' => ['en' => '25g per 100g', 'ar' => '25غ لكل 100غ'], 'price' => 0],
                ]
            ],
            [
                'category_id' => $lentilsCategory?->id ?? 3,
                'vendor_id' => 1,
                'brand_id' => 1,
                'name' => ['en' => 'Green Lentils', 'ar' => 'عدس أخضر'],
                'description' => ['en' => 'Premium green lentils', 'ar' => 'عدس أخضر فاخر'],
                'full_description' => [
                    'en' => 'Premium green lentils with firm texture. Perfect for salads, side dishes, and hearty meals. Holds shape well when cooked. Earthy, peppery flavor.',
                    'ar' => 'عدس أخضر فاخر بقوام متماسك. مثالي للسلطات والأطباق الجانبية والوجبات الدسمة. يحافظ على شكله عند الطبخ. نكهة ترابية وفلفلية.'
                ],
                'sku' => 'LEN-GRN-007',
                'country_id' => $countries['France'] ?? null,
                'sale_country_id' => $saleCountries['Kuwait'] ?? null,
                'model' => 'LENTILS-GREEN',
                'price' => 15,
                'discount' => 0,
                'quantity' => 100,
                'barcode' => '8901234567896',
                'time_prepare' => '00:25:00',
                'bought_with' => [],
                'is_instant_delivery' => false,
                'approval_status' => 'approved',
                'warranty_period' => 24,
                'seo_title' => ['en' => 'Green Lentils - Premium Quality', 'ar' => 'عدس أخضر - جودة فاخرة'],
                'seo_description' => ['en' => 'French green lentils for salads', 'ar' => 'عدس أخضر فرنسي للسلطات'],
                'seo_keywords' => ['en' => 'lentils, green, french, salad', 'ar' => 'عدس، أخضر، فرنسي، سلطة'],
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '1 kg', 'ar' => '1 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Type', 'ar' => 'النوع'], 'value' => ['en' => 'Whole Green', 'ar' => 'أخضر كامل'], 'price' => 0],
                    ['key' => ['en' => 'Texture', 'ar' => 'القوام'], 'value' => ['en' => 'Firm', 'ar' => 'متماسك'], 'price' => 0],
                ]
            ],
            [
                'category_id' => $lentilsCategory?->id ?? 3,
                'vendor_id' => 1,
                'brand_id' => 2,
                'name' => ['en' => 'Brown Lentils', 'ar' => 'عدس بني'],
                'description' => ['en' => 'Versatile brown lentils', 'ar' => 'عدس بني متعدد الاستخدامات'],
                'full_description' => [
                    'en' => 'Versatile brown lentils, the most common variety. Great for soups, stews, and everyday cooking. Mild, earthy flavor. Budget-friendly and nutritious.',
                    'ar' => 'عدس بني متعدد الاستخدامات، النوع الأكثر شيوعاً. رائع للشوربات واليخنات والطبخ اليومي. نكهة ترابية خفيفة. اقتصادي ومغذي.'
                ],
                'sku' => 'LEN-BRN-008',
                'country_id' => $countries['Turkey'] ?? null,
                'sale_country_id' => $saleCountries['Qatar'] ?? null,
                'model' => 'LENTILS-BROWN',
                'price' => 95,
                'discount' => 10,
                'quantity' => 220,
                'barcode' => '8901234567897',
                'time_prepare' => '00:20:00',
                'bought_with' => [],
                'is_instant_delivery' => true,
                'approval_status' => 'approved',
                'warranty_period' => 24,
                'seo_title' => ['en' => 'Brown Lentils - Everyday Cooking', 'ar' => 'عدس بني - للطبخ اليومي'],
                'seo_description' => ['en' => 'Versatile brown lentils', 'ar' => 'عدس بني متعدد الاستخدامات'],
                'seo_keywords' => ['en' => 'lentils, brown, versatile, soup', 'ar' => 'عدس، بني، متعدد، شوربة'],
                'extra_details' => [
                    ['key' => ['en' => 'Weight', 'ar' => 'الوزن'], 'value' => ['en' => '1 kg', 'ar' => '1 كغ'], 'price' => 0],
                    ['key' => ['en' => 'Type', 'ar' => 'النوع'], 'value' => ['en' => 'Whole Brown', 'ar' => 'بني كامل'], 'price' => 0],
                    ['key' => ['en' => 'Uses', 'ar' => 'الاستخدامات'], 'value' => ['en' => 'All Purpose', 'ar' => 'جميع الأغراض'], 'price' => 0],
                ]
            ],
        ];

        $createdProducts = [];

        // First pass: Create all products
        foreach ($products as $productData) {
            $extraDetails = $productData['extra_details'] ?? [];
            unset($productData['extra_details']);

            $product = Product::create($productData);
            $createdProducts[] = $product;

            // Update stock & max_purchase_quantity after creation
            $product->update([
                'stock'                 => rand(50, 500),
                'max_purchase_quantity' => rand(5, 20),
            ]);

            // Add extra details
            foreach ($extraDetails as $detail) {
                ProductExtraDetail::create([
                    'product_id' => $product->id,
                    'detail_key' => $detail['key'],
                    'detail_value' => $detail['value'],
                    'price' => $detail['price'] ?? 0,
                ]);
            }

            // Attach random media
            $this->attachRandomMedia($product, $files);

            // Attach badges
            $product->badges()->sync([1, 2]);

            // Attach random icons (1-3 icons) - only if icons exist
            $availableIcons = \App\Models\Icon::pluck('id')->toArray();
            if (!empty($availableIcons)) {
                shuffle($availableIcons);
                $selectedIcons = array_slice($availableIcons, 0, min(rand(1, 3), count($availableIcons)));
                if (!empty($selectedIcons)) {
                    $product->icons()->attach($selectedIcons);
                }
            }
        }

        // Second pass: Update bought_with relationships
        foreach ($createdProducts as $product) {
            // Get products from same or related categories
            $relatedProducts = collect($createdProducts)
                ->where('id', '!=', $product->id)
                ->shuffle()
                ->take(rand(2, 4))
                ->pluck('id')
                ->toArray();

            if (!empty($relatedProducts)) {
                $product->update(['bought_with' => $relatedProducts]);
            }
        }
    }

    private function attachRandomMedia($product, $files)
    {
        if (empty($files)) {
            return;
        }

        collect($files)->shuffle()->take(rand(2, 4))->values()->each(function ($file, $index) use ($product) {
            ProductMedia::create([
                'mediable_id' => $product->id,
                'mediable_type' => Product::class,
                'collection' => 'product',
                'path' => $file,
                'order' => $index + 1,
            ]);
        });
    }
}
