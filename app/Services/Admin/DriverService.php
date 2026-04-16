<?php

namespace App\Services\Admin;

use App\Http\Resources\Driver\AllResource;
use App\Http\Resources\Driver\OneResource;

use App\Models\Driver;
use App\Services\BaseService;

class DriverService extends BaseService
{
    public function __construct(Driver $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'phone', 'email', 'address', 'status', 'vehicle_type', 'vehicle_name'];
        $this->syncRelations = [
            'cities'  => 'city_ids',
            'shops'   => 'shop_ids',
            'vendors' => 'vendor_ids',
        ];
        $this->relations = ['cities', 'shops', 'vendors'];

        $this->singleImages = ['image', 'vehicle_image'];
    }
}
