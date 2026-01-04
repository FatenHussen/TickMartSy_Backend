<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\CategoryDetail;

class CategoryDetailSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            CategoryDetail::create([
                'category_id' => $category->id,
                'name' => [
                    'en' => 'Material',
                    'ar' => 'الخامة',
                ],
            ]);

            CategoryDetail::create([
                'category_id' => $category->id,
                'name' => [
                    'en' => 'Warranty',
                    'ar' => 'الضمان',
                ],
            ]);
        }
    }
}
