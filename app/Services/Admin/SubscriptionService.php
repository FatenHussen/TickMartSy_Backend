<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Subscription\AllResource;
use App\Http\Resources\Admin\Subscription\OneResource;
use App\Models\Subscription;
use App\Services\BaseService;

class SubscriptionService extends BaseService
{
    protected $model = Subscription::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'user',
        'package',
    ];

    protected $searchableFields = [
        'id',
        'status',
    ];

    protected $sortableFields = [
        'id',
        'start_date',
        'end_date',
        'created_at',
        'status',
    ];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by user_id
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
            unset($filters['user_id']);
        }

        // Filter by package_id
        if (!empty($filters['package_id'])) {
            $query->where('package_id', $filters['package_id']);
            unset($filters['package_id']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
            unset($filters['status']);
        }

        // Filter by date range
        if (!empty($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
            unset($filters['start_date_from']);
        }

        if (!empty($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
            unset($filters['start_date_to']);
        }

        // Apply parent query builder
        return parent::queryBuilder($query, $filters, $config);
    }
}
