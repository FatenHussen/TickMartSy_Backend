<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Unit\AllResource;
use App\Http\Resources\Admin\Unit\OneResource;
use App\Models\Unit;
use App\Services\BaseService;

class UnitService extends BaseService
{
    public function __construct(Unit $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name'];
        $this->sortableFields = ['id', 'is_active', 'created_at'];
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

        return parent::queryBuilder($query, $filters, $config);
    }
}
