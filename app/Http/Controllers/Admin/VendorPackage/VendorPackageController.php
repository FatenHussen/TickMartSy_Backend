<?php

namespace App\Http\Controllers\Admin\VendorPackage;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\VendorPackage\FilterRequest;
use App\Http\Requests\Admin\VendorPackage\StoreRequest;
use App\Http\Requests\Admin\VendorPackage\UpdateRequest;
use App\Services\Admin\VendorPackageService;

class VendorPackageController extends BaseCRUDController
{
    public function __construct(VendorPackageService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
