<?php

namespace App\Services\Admin;

use App\Models\VendorService;
use App\Services\BaseService;

class VendorServiceService extends BaseService
{
    protected $model      = VendorService::class;
    protected $relations  = ['type'];
    protected $sortableFields = ['id', 'created_at'];
}
