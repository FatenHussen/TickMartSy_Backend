<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Driver\FilterRequest;
use App\Http\Requests\Admin\Driver\StoreRequest;
use App\Http\Requests\Admin\Driver\UpdateRequest;
use App\Services\Admin\DriverService;

class DriverCrudController extends BaseCRUDController
{
    public function __construct(
        DriverService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
