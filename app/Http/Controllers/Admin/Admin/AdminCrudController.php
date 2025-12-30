<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Admin\FilterRequest;
use App\Http\Requests\Admin\Admin\StoreRequest;
use App\Http\Requests\Admin\Admin\UpdateRequest;
use App\Services\Admin\AdminService;

class AdminCrudController extends BaseCRUDController
{
    public function __construct(
        AdminService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
