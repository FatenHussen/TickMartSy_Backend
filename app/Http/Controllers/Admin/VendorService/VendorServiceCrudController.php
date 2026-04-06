<?php

namespace App\Http\Controllers\Admin\VendorService;

use App\Http\Controllers\BaseCRUDController;
use App\Services\Admin\VendorServiceService;

class VendorServiceCrudController extends BaseCRUDController
{
    public function __construct(VendorServiceService $service)
    {
        $this->service       = $service;
        $this->createRequest = \App\Http\Requests\Admin\VendorService\StoreRequest::class;
        $this->updateRequest = \App\Http\Requests\Admin\VendorService\UpdateRequest::class;
    }
}
