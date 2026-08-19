<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\Admin\CategoryPageService;

/**
 * Keeps each category paired with a Page Builder page.
 * (Page removal is handled by the pages.category_id cascade on delete.)
 */
class CategoryObserver
{
    public function __construct(
        private CategoryPageService $categoryPageService,
    ) {}

    public function created(Category $category): void
    {
        $this->categoryPageService->syncForCategory($category);
    }

    public function updated(Category $category): void
    {
        if ($category->wasChanged('name')) {
            $this->categoryPageService->syncTitle($category);
        }
    }
}
