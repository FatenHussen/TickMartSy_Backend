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
                    ['en' => '#fc0303', 'ar' => '#fc0303'],
                    ['en' => '#0303fc', 'ar' => '#0303fc'],
                    ['en' => '#00000a', 'ar' => '#00000a'],
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
