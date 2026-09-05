<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Warranty\AllResource;
use App\Http\Resources\Admin\Warranty\OneResource;
use App\Models\Warranty;
use App\Services\BaseService;

class WarrantyService extends BaseService
{
    public function __construct(Warranty $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'description'];
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
