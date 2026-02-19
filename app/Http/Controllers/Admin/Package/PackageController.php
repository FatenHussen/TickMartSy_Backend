<?php

namespace App\Http\Controllers\Admin\Package;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Package\FilterRequest;
use App\Http\Requests\Admin\Package\StoreRequest;
use App\Http\Requests\Admin\Package\UpdateRequest;
use App\Services\Admin\PackageService;

class PackageController extends BaseCRUDController
{
    public function __construct(PackageService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
