<?php

namespace App\Http\Controllers\Admin\VendorWithdrawRequest;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\VendorWithdrawRequest\FilterRequest;
use App\Http\Requests\Admin\VendorWithdrawRequest\UpdateRequest;
use App\Services\Admin\VendorWithdrawRequestService;

class VendorWithdrawRequestController extends BaseCRUDController
{
    public function __construct(VendorWithdrawRequestService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
