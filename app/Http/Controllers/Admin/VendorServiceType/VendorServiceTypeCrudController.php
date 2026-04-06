<?php

namespace App\Http\Controllers\Admin\VendorServiceType;

use App\Http\Controllers\BaseCRUDController;
use App\Services\Admin\VendorServiceTypeService;

class VendorServiceTypeCrudController extends BaseCRUDController
{
    public function __construct(VendorServiceTypeService $service)
    {
        $this->service       = $service;
        $this->createRequest = \App\Http\Requests\Admin\VendorServiceType\StoreRequest::class;
        $this->updateRequest = \App\Http\Requests\Admin\VendorServiceType\UpdateRequest::class;
    }
}
