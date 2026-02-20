<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Gift\AllResource;
use App\Http\Resources\Admin\Gift\OneResource;
use App\Models\Gift;
use App\Services\BaseService;

class GiftService extends BaseService
{
    protected $model = Gift::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [];

    protected $singleImages = ['image'];

    protected $searchableFields = [
        'id',
        'name',
        'description',
    ];

    protected $sortableFields = [
        'id',
        'name',
        'points_required',
        'stock_quantity',
        'created_at',
    ];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by is_active
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
            unset($filters['is_active']);
        }

        // Filter by points range
        if (!empty($filters['points_min'])) {
            $query->where('points_required', '>=', $filters['points_min']);
            unset($filters['points_min']);
        }

        if (!empty($filters['points_max'])) {
            $query->where('points_required', '<=', $filters['points_max']);
            unset($filters['points_max']);
        }

        // Filter by availability
        if (isset($filters['available']) && $filters['available']) {
            $query->available();
            unset($filters['available']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }
}
