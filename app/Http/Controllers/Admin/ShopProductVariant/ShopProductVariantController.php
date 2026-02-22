<?php

namespace App\Http\Controllers\Admin\ShopProductVariant;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\Admin\ShopProductVariant\FilterRequest;
use App\Services\Admin\ShopProductVariantService;

class ShopProductVariantController extends BaseIndexController
{
    public function __construct(ShopProductVariantService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }
}
