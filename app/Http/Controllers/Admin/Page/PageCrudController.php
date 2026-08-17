<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Page\AddSectionRequest;
use App\Http\Requests\Admin\Page\FilterRequest;
use App\Http\Requests\Admin\Page\StoreRequest;
use App\Http\Requests\Admin\Page\UpdateRequest;
use App\Services\Admin\PageService;

class PageCrudController extends BaseCRUDController
{
    public function __construct(
        PageService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
        $this->filterRequest = FilterRequest::class;
    }

    /**
     * Unified endpoint: create a section and attach it to the page in one call.
     */
    public function addSection(AddSectionRequest $request, int $page)
    {
        $data = $this->service->addSection($page, $request->validated());

        return $this->sendResponse(
            data: $data,
            message: 'Section added to page successfully'
        );
    }
}
