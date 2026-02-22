<?php

namespace App\Http\Controllers\Admin\VendorSubscription;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\VendorSubscription\FilterRequest;
use App\Http\Requests\Admin\VendorSubscription\StoreRequest;
use App\Http\Requests\Admin\VendorSubscription\UpdateRequest;
use App\Services\Admin\VendorSubscriptionService;

class VendorSubscriptionController extends BaseCRUDController
{
    public function __construct(VendorSubscriptionService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
