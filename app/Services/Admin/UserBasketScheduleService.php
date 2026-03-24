<?php

namespace App\Services\Admin;

use App\Models\UserBasketSchedule;
use App\Services\BaseService;
use App\Http\Resources\Admin\UserBasketSchedule\OneResource;
use App\Http\Resources\Admin\UserBasketSchedule\AllResource;

class UserBasketScheduleService extends BaseService
{
    protected $model = UserBasketSchedule::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'user',
        'schedule',
        'items.product',
        'items.variant',
    ];

    protected $searchableFields = [
        'name',
        'user_id',
    ];

    protected $sortableFields = [
        'id',
        'name',
        'user_id',
        'is_active',
        'start_date',
        'next_run_date',
        'created_at',
    ];

    /**
     * Override queryBuilder to add custom filters
     */
    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by user_id
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by schedule_id
        if (!empty($filters['schedule_id'])) {
            $query->where('schedule_id', $filters['schedule_id']);
        }

        // Filter by is_active
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filter by date range
        if (!empty($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
        }

        if (!empty($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }




}
