<?php

namespace App\Http\Controllers\Admin\ShopProductVariant;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\ShopProductVariant\FilterRequest;
use App\Http\Requests\Admin\ShopProductVariant\UpdateRequest;
use App\Services\Admin\ShopProductVariantService;

class ShopProductVariantController extends BaseCRUDController
{
    public function __construct(ShopProductVariantService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
