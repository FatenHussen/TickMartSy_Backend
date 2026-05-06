<?php

namespace App\Http\Controllers\Admin\Subscription;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Subscription\FilterRequest;
use App\Http\Requests\Admin\Subscription\StoreRequest;
use App\Http\Requests\Admin\Subscription\UpdateRequest;
use App\Services\Admin\SubscriptionService;

class SubscriptionController extends BaseCRUDController
{
    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
