<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\ShopVendorService\AllResource;
use App\Http\Resources\Admin\ShopVendorService\OneResource;
use App\Models\ShopVendorService;
use App\Services\BaseService;

class ShopVendorServiceService extends BaseService
{
    protected $model      = ShopVendorService::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;
    protected $relations  = ['shop', 'vendorService.type'];
    protected $sortableFields = ['id', 'created_at'];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['shop_id'])) {
            $query->where('shop_id', $filters['shop_id']);
            unset($filters['shop_id']);
        }

        if (!empty($filters['vendor_service_id'])) {
            $query->where('vendor_service_id', $filters['vendor_service_id']);
            unset($filters['vendor_service_id']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }
}
