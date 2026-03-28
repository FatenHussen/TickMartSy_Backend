<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryAttribute;
use App\Models\AttributeValue;

class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = CategoryAttribute::with('category')->get();

        foreach ($attributes as $attribute) {
            $attributeName = $attribute->getTranslation('name', 'en');
            $values = [];

            // Define values based on attribute name
            switch ($attributeName) {
                case 'Color':
                    $values = [
                        ['en' => '#FF0000', 'ar' => '#FF0000'], // Red
                        ['en' => '#0000FF', 'ar' => '#0000FF'], // Blue
                        ['en' => '#000000', 'ar' => '#000000'], // Black
                        ['en' => '#FFFFFF', 'ar' => '#FFFFFF'], // White
                        ['en' => '#00FF00', 'ar' => '#00FF00'], // Green
                    ];
                    break;

                case 'Storage':
                    $values = [
                        ['en' => '64GB', 'ar' => '64 جيجا'],
                        ['en' => '128GB', 'ar' => '128 جيجا'],
                        ['en' => '256GB', 'ar' => '256 جيجا'],
                        ['en' => '512GB', 'ar' => '512 جيجا'],
                    ];
                    break;

                case 'RAM':
                    $values = [
                        ['en' => '4GB', 'ar' => '4 جيجا'],
                        ['en' => '6GB', 'ar' => '6 جيجا'],
                        ['en' => '8GB', 'ar' => '8 جيجا'],
                        ['en' => '12GB', 'ar' => '12 جيجا'],
                    ];
                    break;

                case 'Size':
                    $values = [
                        ['en' => 'XS', 'ar' => 'صغير جداً'],
                        ['en' => 'S', 'ar' => 'صغير'],
                        ['en' => 'M', 'ar' => 'وسط'],
                        ['en' => 'L', 'ar' => 'كبير'],
                        ['en' => 'XL', 'ar' => 'كبير جداً'],
                        ['en' => 'XXL', 'ar' => 'كبير جداً جداً'],
                    ];
                    break;

                case 'Material':
                    $values = [
                        ['en' => 'Cotton', 'ar' => 'قطن'],
                        ['en' => 'Polyester', 'ar' => 'بوليستر'],
                        ['en' => 'Denim', 'ar' => 'دينم'],
                        ['en' => 'Silk', 'ar' => 'حرير'],
                        ['en' => 'Wool', 'ar' => 'صوف'],
                    ];
                    break;

                case 'Weight':
                    $values = [
                        ['en' => '500g', 'ar' => '500 غرام'],
                        ['en' => '1kg', 'ar' => '1 كيلو'],
                        ['en' => '2kg', 'ar' => '2 كيلو'],
                        ['en' => '5kg', 'ar' => '5 كيلو'],
                        ['en' => '10kg', 'ar' => '10 كيلو'],
                    ];
                    break;

                case 'Quality':
                    $values = [
                        ['en' => 'Premium', 'ar' => 'فاخر'],
                        ['en' => 'Standard', 'ar' => 'عادي'],
                        ['en' => 'Economy', 'ar' => 'اقتصادي'],
                    ];
                    break;

                case 'Grain Size':
                    $values = [
                        ['en' => 'Fine', 'ar' => 'ناعم'],
                        ['en' => 'Medium', 'ar' => 'وسط'],
                        ['en' => 'Coarse', 'ar' => 'خشن'],
                    ];
                    break;

                case 'Type':
                    $values = [
                        ['en' => 'Red', 'ar' => 'أحمر'],
                        ['en' => 'Green', 'ar' => 'أخضر'],
                        ['en' => 'Brown', 'ar' => 'بني'],
                        ['en' => 'Yellow', 'ar' => 'أصفر'],
                    ];
                    break;

                default:
                    // Default values if attribute name doesn't match
                    $values = [
                        ['en' => 'Option 1', 'ar' => 'خيار 1'],
                        ['en' => 'Option 2', 'ar' => 'خيار 2'],
                        ['en' => 'Option 3', 'ar' => 'خيار 3'],
                    ];
                    break;
            }

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
