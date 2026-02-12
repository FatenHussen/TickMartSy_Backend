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
        'category',
        'schedule',
        'items.product',
        'items.variant',
    ];

    protected $searchableFields = [
        'name',
        'user_id',
        'category_id',
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

        // Filter by category_id
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
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

    /**
     * Get statistics for user basket schedules
     */
    public function getStatistics(): array
    {
        $total = UserBasketSchedule::count();
        $active = UserBasketSchedule::where('is_active', true)->count();
        $inactive = UserBasketSchedule::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    /**
     * Get user basket schedules grouped by user
     */
    public function getByUser($userId)
    {
        $baskets = UserBasketSchedule::with($this->relations)
            ->where('user_id', $userId)
            ->get();

        return ($this->collection)::collection($baskets);
    }

    /**
     * Get user basket schedules grouped by schedule
     */
    public function getBySchedule($scheduleId)
    {
        $baskets = UserBasketSchedule::with($this->relations)
            ->where('schedule_id', $scheduleId)
            ->get();

        return ($this->collection)::collection($baskets);
    }
}
