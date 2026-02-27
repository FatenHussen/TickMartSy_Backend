<?php

namespace App\Http\Controllers\Admin\PageSection;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\PageSection\FilterRequest;
use App\Http\Requests\Admin\PageSection\StoreRequest;
use App\Http\Requests\Admin\PageSection\UpdateRequest;
use App\Services\Admin\PageSectionService;

class PageSectionCrudController extends BaseCRUDController
{
    public function __construct(
        PageSectionService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }

    public function displayTypes($manual_model)
    {
        $data =  $this->service->displayTypes($manual_model);
        return $this->sendResponse(data: $data);
    }
}
