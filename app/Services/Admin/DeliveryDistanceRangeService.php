<?php

namespace App\Services\Admin;

use App\Http\Resources\DeliveryDistanceRange\AllResource;
use App\Http\Resources\DeliveryDistanceRange\OneResource;
use App\Models\DeliveryDistanceRange;
use App\Services\BaseService;

class DeliveryDistanceRangeService extends BaseService
{
    public function __construct(DeliveryDistanceRange $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = false;
        $this->searchableFields = ['id'];
        $this->sortableFields = ['id', 'min_distance', 'max_distance', 'multiplier'];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        return $query
            ->orderBy('min_distance')
            ->orderByRaw('CASE WHEN max_distance IS NULL THEN 1 ELSE 0 END')
            ->orderBy('max_distance')
            ->orderBy('id');
    }
}
