<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Shop\StoreRequest;
use App\Http\Requests\Admin\Shop\UpdateRequest;
use App\Services\Admin\ShopService;

class ShopCrudController extends BaseCRUDController
{
    public function __construct(
        ShopService $service
    ) {
        $this->service = $service;
        // $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
