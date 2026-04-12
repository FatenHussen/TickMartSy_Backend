<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\VendorService\AllResource;
use App\Http\Resources\Admin\VendorService\OneResource;
use App\Models\VendorService;
use App\Services\BaseService;

class VendorServiceService extends BaseService
{
    protected $model      = VendorService::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;
    protected $relations  = ['type'];
    protected $sortableFields = ['id', 'created_at'];
}
