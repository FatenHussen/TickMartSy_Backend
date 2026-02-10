<?php

namespace App\Http\Controllers\Admin\Coupon;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Coupon\StoreRequest;
use App\Http\Requests\Admin\Coupon\UpdateRequest;
use App\Services\Admin\CouponService;
use App\Services\Admin\VendorService;

class CouponCrudController extends BaseCRUDController
{
    public function __construct(
        CouponService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
