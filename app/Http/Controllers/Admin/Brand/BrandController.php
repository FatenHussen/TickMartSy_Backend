<?php

namespace App\Http\Controllers\Admin\Brand;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brand\FilterRequest;
use App\Http\Requests\Admin\Brand\StoreRequest;
use App\Http\Requests\Admin\Brand\UpdateRequest;
use App\Services\Admin\BrandService;

class BrandController extends BaseCRUDController
{
    public function __construct(BrandService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
