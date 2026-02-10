<?php

namespace App\Http\Controllers\Admin\Complaint;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\Admin\Complaint\FilterRequest;
use App\Http\Requests\Admin\Complaint\UpdateRequest;
use App\Http\Requests\User\Shop\ShopRequest;
use App\Services\Admin\ComplaintService;

class ComplaintController extends BaseCRUDController
{
    public function __construct(ComplaintService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
    public function show($id)
    {
        $res = $this->service->getOne($id);
        return $this->sendResponse($res);
    }
}
