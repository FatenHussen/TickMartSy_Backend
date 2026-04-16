<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\DriverWalletTransaction\AllResource;
use App\Http\Resources\Admin\DriverWalletTransaction\OneResource;
use App\Models\DriverWalletTransaction;
use App\Services\BaseService;

class DriverWalletTransactionService extends BaseService
{
    protected $model = DriverWalletTransaction::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = ['driver', 'order'];
    protected $searchableFields = ['id', 'driver_id', 'type', 'order_id'];
    protected $sortableFields = ['id', 'created_at', 'amount', 'type'];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
            unset($filters['type']);
        }

        if (!empty($filters['driver_id'])) {
            $query->where('driver_id', $filters['driver_id']);
            unset($filters['driver_id']);
        }

        if (!empty($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
            unset($filters['order_id']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
            unset($filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
            unset($filters['to']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
            unset($filters['min_amount']);
        }

        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
            unset($filters['max_amount']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }
}
