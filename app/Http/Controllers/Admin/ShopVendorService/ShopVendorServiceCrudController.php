<?php

namespace App\Http\Controllers\Admin\ShopVendorService;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\ShopVendorService\StoreRequest;
use App\Http\Requests\Admin\ShopVendorService\UpdateRequest;
use App\Services\Admin\ShopVendorServiceService;

class ShopVendorServiceCrudController extends BaseCRUDController
{
    public function __construct(ShopVendorServiceService $service)
    {
        $this->service       = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
