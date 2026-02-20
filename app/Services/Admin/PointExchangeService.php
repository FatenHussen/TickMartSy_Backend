<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\PointExchange\AllResource;
use App\Http\Resources\Admin\PointExchange\OneResource;
use App\Models\PointExchange;
use App\Services\BaseService;

class PointExchangeService extends BaseService
{
    protected $model = PointExchange::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'user',
        'transaction',
        'transaction.rule',
    ];

    protected $searchableFields = [
        'id',
        'exchange_type',
        'status',
    ];

    protected $sortableFields = [
        'id',
        'created_at',
        'delivered_at',
        'status',
    ];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by user_id
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
            unset($filters['user_id']);
        }

        // Filter by exchange_type
        if (!empty($filters['exchange_type'])) {
            $query->where('exchange_type', $filters['exchange_type']);
            unset($filters['exchange_type']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
            unset($filters['status']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
            unset($filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
            unset($filters['date_to']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }
}
