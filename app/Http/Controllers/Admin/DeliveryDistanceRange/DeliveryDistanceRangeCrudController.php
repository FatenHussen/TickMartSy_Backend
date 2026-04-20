<?php

namespace App\Http\Controllers\Admin\DeliveryDistanceRange;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\DeliveryDistanceRange\StoreRequest;
use App\Http\Requests\Admin\DeliveryDistanceRange\UpdateRequest;
use App\Services\Admin\DeliveryDistanceRangeService;

class DeliveryDistanceRangeCrudController extends BaseCRUDController
{
    public function __construct(DeliveryDistanceRangeService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
