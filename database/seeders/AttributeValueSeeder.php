<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryAttribute;
use App\Models\AttributeValue;
use App\Models\Color;

class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = CategoryAttribute::with('category')->get();

        foreach ($attributes as $attribute) {
            $attributeName = $attribute->getTranslation('name', 'en');
            $attributeType = $attribute->type;

            // If attribute type is 'color', use colors from colors table
            if ($attributeType === 'color') {
                $colors = Color::all();
                foreach ($colors as $color) {
                    AttributeValue::create([
                        'category_attribute_id' => $attribute->id,
                        'name' => ['en' => $color->hex, 'ar' => $color->hex], // Store hex code in both languages
                        'color_id' => $color->id,
                    ]);
                }
                continue;
            }

            // For non-color attributes, use predefined values
            $values = match ($attributeName) {
                'Storage' => [
                    ['en' => '64GB', 'ar' => '64 جيجا'],
                    ['en' => '128GB', 'ar' => '128 جيجا'],
                    ['en' => '256GB', 'ar' => '256 جيجا'],
                    ['en' => '512GB', 'ar' => '512 جيجا'],
                ],
                'RAM' => [
                    ['en' => '4GB', 'ar' => '4 جيجا'],
                    ['en' => '6GB', 'ar' => '6 جيجا'],
                    ['en' => '8GB', 'ar' => '8 جيجا'],
                    ['en' => '12GB', 'ar' => '12 جيجا'],
                ],
                'Size' => [
                    ['en' => 'XS', 'ar' => 'صغير جداً'],
                    ['en' => 'S', 'ar' => 'صغير'],
                    ['en' => 'M', 'ar' => 'وسط'],
                    ['en' => 'L', 'ar' => 'كبير'],
                    ['en' => 'XL', 'ar' => 'كبير جداً'],
                    ['en' => 'XXL', 'ar' => 'كبير جداً جداً'],
                ],
                'Material' => [
                    ['en' => 'Cotton', 'ar' => 'قطن'],
                    ['en' => 'Polyester', 'ar' => 'بوليستر'],
                    ['en' => 'Denim', 'ar' => 'دينم'],
                    ['en' => 'Silk', 'ar' => 'حرير'],
                    ['en' => 'Wool', 'ar' => 'صوف'],
                ],
                'Weight' => [
                    ['en' => '500g', 'ar' => '500 غرام'],
                    ['en' => '1kg', 'ar' => '1 كيلو'],
                    ['en' => '2kg', 'ar' => '2 كيلو'],
                    ['en' => '5kg', 'ar' => '5 كيلو'],
                    ['en' => '10kg', 'ar' => '10 كيلو'],
                ],
                'Quality' => [
                    ['en' => 'Premium', 'ar' => 'فاخر'],
                    ['en' => 'Standard', 'ar' => 'عادي'],
                    ['en' => 'Economy', 'ar' => 'اقتصادي'],
                ],
                'Grain Size' => [
                    ['en' => 'Fine', 'ar' => 'ناعم'],
                    ['en' => 'Medium', 'ar' => 'وسط'],
                    ['en' => 'Coarse', 'ar' => 'خشن'],
                ],
                'Type' => [
                    ['en' => 'Red', 'ar' => 'أحمر'],
                    ['en' => 'Green', 'ar' => 'أخضر'],
                    ['en' => 'Brown', 'ar' => 'بني'],
                    ['en' => 'Yellow', 'ar' => 'أصفر'],
                ],
                default => [
                    ['en' => 'Option 1', 'ar' => 'خيار 1'],
                    ['en' => 'Option 2', 'ar' => 'خيار 2'],
                    ['en' => 'Option 3', 'ar' => 'خيار 3'],
                ],
            };

            // Create attribute values
            foreach ($values as $value) {
                AttributeValue::create([
                    'category_attribute_id' => $attribute->id,
                    'name' => $value,
                ]);
            }
        }
    }
}
