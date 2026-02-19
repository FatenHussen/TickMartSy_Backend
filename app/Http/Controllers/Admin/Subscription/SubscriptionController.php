<?php

namespace App\Http\Controllers\Admin\Subscription;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Subscription\FilterRequest;
use App\Services\Admin\SubscriptionService;

class SubscriptionController extends BaseCRUDController
{
    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }
}
