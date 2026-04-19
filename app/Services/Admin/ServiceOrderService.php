<?php

namespace App\Services\Admin;

use App\Enums\ServiceOrderStatus;
use App\Exceptions\CustomExceptionWithMessage;
use App\Models\ServiceOrder;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class ServiceOrderService extends BaseService
{
    public function __construct(ServiceOrder $model)
    {
        $this->model = $model;
        $this->resource = \App\Http\Resources\ServiceOrder\OneResource::class;
        $this->collection = \App\Http\Resources\ServiceOrder\AllResource::class;
        $this->relations = ['shop', 'vendorService', 'shopVendorService', 'user'];
        $this->pagination = true;
        $this->searchableFields = ['id', 'status'];
        $this->sortableFields = ['id', 'price', 'created_at', 'date'];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['shop_id'])) {
            $query->where('shop_id', $filters['shop_id']);
        }

        if (! empty($filters['vendor_service_id'])) {
            $query->where('vendor_service_id', $filters['vendor_service_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query;
    }

    public function changeStatus(int $orderId, string $status)
    {
        return DB::transaction(function () use ($orderId, $status) {
            $order = $this->model
                ->with($this->relations)
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->status === $status) {
                return $order;
            }

            if ($this->isFinalStatus($order->status)) {
                throw new CustomExceptionWithMessage('custom.service_orders.cannot_change_final_status');
            }

            $order->update(['status' => $status]);

            return $order->fresh($this->relations);
        });
    }

    private function isFinalStatus(string $status): bool
    {
        return in_array($status, [
            ServiceOrderStatus::CANCELED->value,
            ServiceOrderStatus::REJECTED->value,
            ServiceOrderStatus::COMPLETED->value,
        ], true);
    }
}
