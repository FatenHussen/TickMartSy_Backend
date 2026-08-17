<?php

namespace App\Http\Controllers\User\Category;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\User\Category\FilterRequest;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Services\User\CategoryService;

class CategoryController extends BaseIndexController
{
    public function __construct(CategoryService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }

    public function attributes(int $categoryId)
    {
        $rootId = Category::resolveRootId($categoryId);

        $attributes = CategoryAttribute::with('values')
            ->forCategoryTree($categoryId)
            ->where('is_active', true)
            ->get()
            ->map(fn($attr) => [
                'id'               => $attr->id,
                'category_id'      => $attr->category_id,
                'root_category_id' => $rootId,
                'name'             => $attr->name,
                'type'             => $attr->type,
                'values'           => $attr->values->map(fn($val) => [
                    'id'   => $val->id,
                    'name' => $val->name,
                ]),
            ]);

        return $this->sendResponse($attributes);
    }
}
