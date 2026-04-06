<?php

namespace App\Services\Admin;

use App\Models\VendorServiceType;
use App\Services\BaseService;

class VendorServiceTypeService extends BaseService
{
    protected $model      = VendorServiceType::class;
    protected $sortableFields = ['id', 'created_at'];
}
