<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\ProductExtraDetail\AllResource;
use App\Http\Resources\Admin\ProductExtraDetail\OneResource;
use App\Models\ProductExtraDetail;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;

class ProductExtraDetailService extends BaseService
{
    protected $model = ProductExtraDetail::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
        'products',
    ];

    protected $searchableFields = [
        'id',
        'category_id',
        'detail_key',
        'detail_value',
    ];

    protected $sortableFields = [
        'id',
        'category_id',
        'created_at',
    ];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Remove 'search' from filters if it exists (it should be in $config, not $filters)
        unset($filters['search']);

        // Handle standard filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (!is_scalar($value)) {
                continue;
            }

            $query->where($key, $value);
        }

        // Handle search - with proper ID support
        if (!empty($config['search'])) {
            $search = $config['search'];

            $query->where(function ($q) use ($search) {
                // Search by ID as exact match
                if (is_numeric($search)) {
                    $q->orWhere('id', '=', (int) $search);
                    $q->orWhere('category_id', '=', (int) $search);
                }

                // Search by detail_key using JSON_SEARCH
                $q->orWhereRaw("JSON_SEARCH(detail_key, 'one', ?, NULL, '$[*]') IS NOT NULL", ["%{$search}%"]);

                // Search by detail_value using JSON_SEARCH
                $q->orWhereRaw("JSON_SEARCH(detail_value, 'one', ?, NULL, '$[*]') IS NOT NULL", ["%{$search}%"]);
            });
        }

        if (!empty($config['sortField']) && in_array($config['sortField'], $this->sortableFields ?? [])) {
            $order = strtolower($config['sortOrder'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($config['sortField'], $order);
        }

        return $query;
    }
}
