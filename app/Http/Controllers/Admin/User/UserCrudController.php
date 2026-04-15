<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\User\FilterRequest;
use App\Http\Requests\Admin\User\ReactivateAffiliateRequest;
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

    public function demoteAffiliate(int $id)
    {
        $data = $this->service->demoteAffiliate($id);

        return $this->sendResponse(
            data: $data,
            message: __('custom.marketer.demoted_successfully')
        );
    }

    public function reactivateAffiliate(int $id, ReactivateAffiliateRequest $request)
    {
        $data = $this->service->reactivateAffiliate($id, $request->validated());

        return $this->sendResponse(
            data: $data,
            message: __('custom.marketer.reactivated_successfully')
        );
    }
}
