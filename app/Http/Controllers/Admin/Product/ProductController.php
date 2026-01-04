<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Product\FilterRequest;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Services\Admin\ProductService;

class ProductController extends BaseCRUDController
{
    public function __construct(ProductService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
