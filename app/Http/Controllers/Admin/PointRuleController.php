<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Point\PointRuleRequest;
use App\Services\Admin\PointRuleService;

class PointRuleController extends BaseCRUDController
{
    public function __construct(PointRuleService $service)
    {
        $this->service = $service;
        $this->createRequest = PointRuleRequest::class;
        $this->updateRequest = PointRuleRequest::class;
    }
}