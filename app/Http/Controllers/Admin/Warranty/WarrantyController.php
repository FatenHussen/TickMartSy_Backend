<?php

namespace App\Http\Controllers\Admin\Warranty;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Warranty\FilterRequest;
use App\Http\Requests\Admin\Warranty\StoreRequest;
use App\Http\Requests\Admin\Warranty\UpdateRequest;
use App\Services\Admin\WarrantyService;

class WarrantyController extends BaseCRUDController
{
    public function __construct(WarrantyService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
