<?php

namespace App\Http\Controllers\Admin\AffiliateWithdrawRequest;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\AffiliateWithdrawRequest\FilterRequest;
use App\Http\Requests\Admin\AffiliateWithdrawRequest\UpdateRequest;
use App\Services\Admin\AffiliateWithdrawRequestService;

class AffiliateWithdrawRequestController extends BaseCRUDController
{
    public function __construct(AffiliateWithdrawRequestService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
