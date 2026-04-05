<?php

namespace App\Http\Controllers\Admin\ProductVariant;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\ProductVariant\FilterRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateRequest;
use App\Services\Admin\ProductVariantService;

class ProductVariantController extends BaseCRUDController
{
    public function __construct(ProductVariantService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
