<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\VendorServiceType\AllResource;
use App\Http\Resources\Admin\VendorServiceType\OneResource;
use App\Models\VendorServiceType;
use App\Services\BaseService;

class VendorServiceTypeService extends BaseService
{
    protected $model      = VendorServiceType::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;
    protected $sortableFields = ['id', 'created_at'];
}
