<?php

namespace App\Services\User;

use App\Enums\ServiceOrderStatus;
use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\ServiceOrder\AllResource;
use App\Http\Resources\ServiceOrder\OneResource;
use App\Models\ServiceOrder;
use App\Models\ShopVendorService;
use App\Services\BaseService;

class ServiceOrderService extends BaseService
{
    public function __construct(ServiceOrder $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = ['shop', 'vendorService', 'shopVendorService', 'user'];
        $this->pagination = true;
        $this->searchableFields = ['id', 'notes'];
        $this->sortableFields = ['id', 'created_at'];
    }

    public function getAll($filters = [], $config = [])
    {
        $filters['user_id'] = auth('user')->id();
        return parent::getAll($filters, $config);
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public function create($data)
    {
        $shopVendorService = ShopVendorService::where('shop_id', $data['shop_id'])
            ->where('vendor_service_id', $data['vendor_service_id'])
            ->where('is_active', true)
            ->first();

        if (!$shopVendorService) {
            throw new CustomExceptionWithMessage('custom.service_orders.service_not_available', 404);
        }

        $order = ServiceOrder::create([
            'user_id' => auth('user')->id(),
            'shop_id' => $shopVendorService->shop_id,
            'vendor_service_id' => $shopVendorService->vendor_service_id,
            'shop_vendor_service_id' => $shopVendorService->id,
            'price' => $shopVendorService->price ?? 0,
            'price_unit' => $shopVendorService->price_unit,
            'notes' => $data['notes'] ?? null,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        return new OneResource($order->load($this->relations));
    }
}
