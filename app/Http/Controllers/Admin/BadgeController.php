<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Badge\FilterRequest;
use App\Http\Requests\Admin\Badge\UpdateRequest;
use App\Http\Requests\Admin\Badge\StoreRequest;
use App\Services\Admin\BadgeService;

class BadgeController extends BaseCRUDController
{
    public function __construct(
        BadgeService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
