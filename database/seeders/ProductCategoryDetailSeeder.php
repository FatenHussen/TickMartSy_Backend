<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\CategoryDetail;
use App\Models\ProductCategoryDetail;

class ProductCategoryDetailSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::with('category')->get();

        foreach ($products as $product) {
            $details = CategoryDetail::where('category_id', $product->category_id)->get();
            $categoryName = $product->category->getTranslation('name', 'en');

            foreach ($details as $detail) {
                $detailName = $detail->getTranslation('name', 'en');
                $value = $this->getDetailValue($categoryName, $detailName);

                ProductCategoryDetail::create([
                    'product_id' => $product->id,
                    'category_detail_id' => $detail->id,
                    'detail_value' => $value,
                ]);
            }
        }
    }

    private function getDetailValue(string $categoryName, string $detailName): array
    {
        // Electronics values
        if (str_contains($categoryName, 'Electronics')) {
            return match($detailName) {
                'Processor' => ['en' => 'Snapdragon 888', 'ar' => 'سنابدراجون 888'],
                'Screen Size' => ['en' => '6.5 inches', 'ar' => '6.5 بوصة'],
                'Battery' => ['en' => '5000 mAh', 'ar' => '5000 ميلي أمبير'],
                'Warranty' => ['en' => '1 Year', 'ar' => 'سنة واحدة'],
                default => ['en' => 'N/A', 'ar' => 'غير متوفر'],
            };
        }

        // Fashion values
        if (str_contains($categoryName, 'Fashion')) {
            return match($detailName) {
                'Material' => ['en' => '100% Cotton', 'ar' => '100% قطن'],
                'Care Instructions' => ['en' => 'Machine washable', 'ar' => 'قابل للغسل بالغسالة'],
                'Country of Origin' => ['en' => 'Turkey', 'ar' => 'تركيا'],
                default => ['en' => 'N/A', 'ar' => 'غير متوفر'],
            };
        }

        // Rice values
        if (str_contains($categoryName, 'Rice')) {
            return match($detailName) {
                'Grain Length' => ['en' => 'Extra Long', 'ar' => 'طويلة جداً'],
                'Cooking Time' => ['en' => '20-25 minutes', 'ar' => '20-25 دقيقة'],
                'Best For' => ['en' => 'Biryani & Pilaf', 'ar' => 'برياني وأرز بالخلطة'],
                'Storage' => ['en' => 'Cool dry place', 'ar' => 'مكان بارد وجاف'],
                default => ['en' => 'N/A', 'ar' => 'غير متوفر'],
            };
        }

        // Bulgur values
        if (str_contains($categoryName, 'Bulgur')) {
            return match($detailName) {
                'Grain Size' => ['en' => 'Fine', 'ar' => 'ناعم'],
                'Preparation Time' => ['en' => '10 minutes', 'ar' => '10 دقائق'],
                'Nutritional Value' => ['en' => 'High in fiber', 'ar' => 'غني بالألياف'],
                default => ['en' => 'N/A', 'ar' => 'غير متوفر'],
            };
        }

        // Lentils values
        if (str_contains($categoryName, 'Lentils')) {
            return match($detailName) {
                'Type' => ['en' => 'Split Red', 'ar' => 'أحمر مقشور'],
                'Cooking Time' => ['en' => '15-20 minutes', 'ar' => '15-20 دقيقة'],
                'Protein Content' => ['en' => '25g per 100g', 'ar' => '25غ لكل 100غ'],
                default => ['en' => 'N/A', 'ar' => 'غير متوفر'],
            };
        }

        // Default value
        return ['en' => 'Standard', 'ar' => 'عادي'];
    }
}
