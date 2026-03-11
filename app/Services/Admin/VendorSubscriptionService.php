<?php

namespace App\Services\Admin;

use App\Models\VendorSubscription;
use App\Services\BaseService;
use App\Http\Resources\Admin\VendorSubscription\AllResource;
use App\Http\Resources\Admin\VendorSubscription\OneResource;

class VendorSubscriptionService extends BaseService
{
    protected $model = VendorSubscription::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;
    protected $relations = ['vendor', 'package'];
    protected $searchableFields = ['id'];
    protected $sortableFields = ['id', 'starts_at', 'ends_at', 'status', 'created_at'];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }
        if (!empty($filters['vendor_package_id'])) {
            $query->where('vendor_package_id', $filters['vendor_package_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['expiring_soon'])) {
            $query->expiringSoon(7);
        }
        return parent::queryBuilder($query, $filters, $config);
    }

    public function create($data)
    {
        $subscription = $this->model::create($data);
        return new $this->resource($subscription->load($this->relations));
    }

    public function update($id, array $data)
    {
        $subscription = $this->model::findOrFail($id);
        $subscription->update($data);
        return new $this->resource($subscription->fresh($this->relations));
    }
}
