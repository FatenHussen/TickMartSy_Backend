<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Vendor\StoreRequest;
use App\Http\Requests\Admin\Vendor\UpdateRequest;
use App\Services\Admin\VendorService;

class VendorCrudController extends BaseCRUDController
{
    public function __construct(
        VendorService $service
    ) {
        $this->service = $service;
        // $this->filterRequest = AdsFilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
