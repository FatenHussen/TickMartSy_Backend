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
                'country' => ['en' => 'India', 'ar' => 'الهند'],
                'model' => 'BASMATI-PREMIUM',
                'price' => 2500,
                'cost_price' => 2000,
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
                'country' => ['en' => 'Egypt', 'ar' => 'مصر'],
                'model' => 'EGYPTIAN-SHORT',
                'price' => 1800,
                'cost_price' => 1400,
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
                'country' => ['en' => 'Thailand', 'ar' => 'تايلاند'],
                'model' => 'JASMINE-THAI',
                'price' => 2200,
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
                'country' => ['en' => 'Syria', 'ar' => 'سوريا'],
                'model' => 'BULGUR-FINE',
                'price' => 800,
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
                'country' => ['en' => 'Turkey', 'ar' => 'تركيا'],
                'model' => 'BULGUR-COARSE',
                'price' => 900,
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
                'country' => ['en' => 'Canada', 'ar' => 'كندا'],
                'model' => 'LENTILS-RED',
                'price' => 1200,
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
                'country' => ['en' => 'France', 'ar' => 'فرنسا'],
                'model' => 'LENTILS-GREEN',
                'price' => 1500,
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
                'country' => ['en' => 'Turkey', 'ar' => 'تركيا'],
                'model' => 'LENTILS-BROWN',
                'price' => 950,
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

        foreach ($products as $productData) {
            $extraDetails = $productData['extra_details'] ?? [];
            unset($productData['extra_details']);

            $product = Product::create($productData);

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
            $product->badges()->attach([
                1 => ['position' => 'top'],
                2 => ['position' => 'bottom'],
            ]);

            // Attach random icons (1-3 icons)
            $iconIds = range(1, 5);
            shuffle($iconIds);
            $selectedIcons = array_slice($iconIds, 0, rand(1, 3));
            $product->icons()->attach($selectedIcons);
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
