<?php

namespace App\Http\Controllers\Admin\Service;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Service\FilterRequest;
use App\Http\Requests\Admin\Service\StoreRequest;
use App\Http\Requests\Admin\Service\UpdateRequest;
use App\Services\Admin\AdminService;
use App\Services\Admin\ServiceService;

class ServiceCrudController extends BaseCRUDController
{
    public function __construct(
        ServiceService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
