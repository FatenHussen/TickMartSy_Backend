<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryAttribute;
use App\Models\AttributeValue;

class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = CategoryAttribute::all();

        foreach ($attributes as $attribute) {

            if ($attribute->getTranslation('name', 'en') === 'Color') {
                $values = [
                    ['en' => 'Red', 'ar' => 'أحمر'],
                    ['en' => 'Blue', 'ar' => 'أزرق'],
                    ['en' => 'Black', 'ar' => 'أسود'],
                ];
            } else {
                $values = [
                    ['en' => 'Small', 'ar' => 'صغير'],
                    ['en' => 'Medium', 'ar' => 'وسط'],
                    ['en' => 'Large', 'ar' => 'كبير'],
                ];
            }

            foreach ($values as $value) {
                AttributeValue::create([
                    'category_attribute_id' => $attribute->id,
                    'name' => $value,
                ]);
            }
        }
    }
}
