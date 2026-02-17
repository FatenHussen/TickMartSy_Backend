<?php

namespace App\Services\User;

use App\Http\Resources\Category\OneResource;
use App\Models\Category;
use App\Services\BaseService;
use App\Http\Resources\Category\AllResource;

class CategoryService extends BaseService
{
    public function __construct(Category $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = ['parent', 'children'];
        $this->pagination = true;
    }

    protected function applyFilters($query, $filters)
    {
        // Apply name filter
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // Apply parent_id filter
        if (array_key_exists('parent_id', $filters)) {
            if ($filters['parent_id'] === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        return $query;
    }
}
