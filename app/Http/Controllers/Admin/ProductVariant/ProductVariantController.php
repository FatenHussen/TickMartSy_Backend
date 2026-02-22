<?php

namespace App\Http\Controllers\Admin\ProductVariant;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\Admin\ProductVariant\FilterRequest;
use App\Services\Admin\ProductVariantService;

class ProductVariantController extends BaseIndexController
{
    public function __construct(ProductVariantService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }
}
