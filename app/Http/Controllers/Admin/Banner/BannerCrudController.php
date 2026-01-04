<?php

namespace App\Http\Controllers\Admin\Banner;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Banner\StoreRequest;
use App\Http\Requests\Admin\Banner\UpdateRequest;
use App\Services\Admin\BannerService;
use App\Services\Admin\ShopService;

class BannerCrudController extends BaseCRUDController
{
    public function __construct(
        BannerService $service
    ) {
        $this->service = $service;
        // $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
