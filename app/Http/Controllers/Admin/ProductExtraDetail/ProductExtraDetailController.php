<?php

namespace App\Http\Controllers\Admin\ProductExtraDetail;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\ProductExtraDetail\FilterRequest;
use App\Http\Requests\Admin\ProductExtraDetail\StoreRequest;
use App\Http\Requests\Admin\ProductExtraDetail\UpdateRequest;
use App\Services\Admin\ProductExtraDetailService;

class ProductExtraDetailController extends BaseCRUDController
{
    public function __construct(ProductExtraDetailService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
