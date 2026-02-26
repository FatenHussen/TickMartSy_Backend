<?php

namespace App\Http\Controllers\Admin\VendorUser;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\VendorUser\StoreVendorUserRequest;
use App\Http\Requests\Admin\VendorUser\UpdateVendorUserRequest;
use App\Services\Admin\VendorUserService;

class VendorUserCrudController extends BaseCRUDController
{
    public function __construct(VendorUserService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreVendorUserRequest::class;
        $this->updateRequest = UpdateVendorUserRequest::class;
    }
}
