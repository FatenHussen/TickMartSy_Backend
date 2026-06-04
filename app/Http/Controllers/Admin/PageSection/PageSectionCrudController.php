<?php

namespace App\Http\Controllers\Admin\PageSection;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\PageSection\FilterRequest;
use App\Http\Requests\Admin\PageSection\ReorderRequest;
use App\Http\Requests\Admin\PageSection\StoreRequest;
use App\Http\Requests\Admin\PageSection\UpdateRequest;
use App\Services\Admin\PageSectionService;
use Illuminate\Http\Request;

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

    public function preview(Request $request, int $page)
    {
        $data = $this->service->previewForPage($page, $request);

        return $this->sendResponse(data: $data);
    }

    public function reorder(ReorderRequest $request, int $page)
    {
        $updated = $this->service->reorderForPage($page, $request->validated('sections'));

        return $this->sendResponse(
            data: ['updated_count' => $updated],
            message: 'Page section order updated successfully'
        );
    }

    public function displayTypes($manual_model)
    {
        $data =  $this->service->displayTypes($manual_model);
        return $this->sendResponse(data: $data);
    }
}
