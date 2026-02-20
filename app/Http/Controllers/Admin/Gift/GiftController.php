<?php

namespace App\Http\Controllers\Admin\Gift;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Gift\FilterRequest;
use App\Http\Requests\Admin\Gift\StoreRequest;
use App\Http\Requests\Admin\Gift\UpdateRequest;
use App\Services\Admin\GiftService;

class GiftController extends BaseCRUDController
{
    public function __construct(GiftService $service)
    {
        $this->service= $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
