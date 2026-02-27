<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\User\FilterRequest;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Services\Admin\CouponService;
use App\Services\Admin\UserService;
use App\Services\Admin\VendorService;

class UserCrudController extends BaseCRUDController
{
    public function __construct(
        UserService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }

    public function markters()
    {
        $data = $this->service->markters();
        return $this->sendResponse(data: $data);
    }
}
