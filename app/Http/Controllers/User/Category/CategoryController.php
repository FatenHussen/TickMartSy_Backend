<?php

namespace App\Http\Controllers\User\Category;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\User\Category\FilterRequest;
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
        $attributes = CategoryAttribute::with('values')
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->get()
            ->map(fn($attr) => [
                'id'     => $attr->id,
                'name'   => $attr->name,
                'type'   => $attr->type,
                'values' => $attr->values->map(fn($val) => [
                    'id'   => $val->id,
                    'name' => $val->name,
                ]),
            ]);

        return $this->sendResponse($attributes);
    }
}
