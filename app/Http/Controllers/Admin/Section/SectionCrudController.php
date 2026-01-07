<?php

namespace App\Http\Controllers\Admin\Section;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Section\FilterRequest;
use App\Http\Requests\Admin\Section\StoreRequest;
use App\Http\Requests\Admin\Section\UpdateRequest;
use App\Services\Admin\SectionService;

class SectionCrudController extends BaseCRUDController
{
    public function __construct(
        SectionService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }
}
