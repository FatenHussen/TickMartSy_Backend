<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Services\Admin\CategoryPageService;
use Illuminate\Database\Seeder;

/**
 * Creates a Page Builder page (with default subcategory + product blocks) for any
 * existing category that doesn't have one yet. Safe to run multiple times.
 */
class CategoryPagesBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(CategoryPageService::class);

        Category::query()
            ->whereDoesntHave('page')
            ->chunkById(200, function ($categories) use ($service) {
                foreach ($categories as $category) {
                    $service->syncForCategory($category);
                }
            });
    }
}
