<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Category\OneResource;
use App\Models\Category;
use App\Services\BaseService;
use App\Http\Resources\Admin\Category\AllResource;

class CategoryService extends BaseService
{
    public function __construct(Category $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->singleImages = ['icon'];
        $this->relations = ['parent', 'children'];
        $this->pagination = true;
        $this->searchableFields = ['name'];
        $this->sortableFields = ['id', 'created_at', 'order'];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['name'])) {
            $search = strtolower(trim((string) $filters['name']));
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$search}%"]);
            });
            unset($filters['name']);
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
            unset($filters['is_active']);
        }

        if (array_key_exists('is_restaurant', $filters) && $filters['is_restaurant'] !== null) {
            $query->where('is_restaurant', (bool) $filters['is_restaurant']);
            unset($filters['is_restaurant']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }
}
