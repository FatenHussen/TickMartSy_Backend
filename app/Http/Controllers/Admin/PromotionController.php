<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Promotion\UpdateRequest;
use App\Http\Requests\Admin\Promotion\StoreRequest;

use App\Services\Admin\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends BaseCRUDController
{
    public function __construct(
        PromotionService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    // public function store(Request $request)
    // {
    //     $data = app($this->createRequest)->validated();
    //     return $res = $this->service->create($data);
    //     return $this->sendResponse(data: $res);
    // }
    public function fieldsForType($type)
    {
        $fields = $this->service->fieldsForType($type);
        return $this->sendResponse(data: $fields);
    }
}
