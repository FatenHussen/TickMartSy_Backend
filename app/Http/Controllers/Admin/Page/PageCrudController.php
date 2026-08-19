<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Page\AddSectionRequest;
use App\Http\Requests\Admin\Page\FilterRequest;
use App\Http\Requests\Admin\Page\StoreRequest;
use App\Http\Requests\Admin\Page\UpdateRequest;
use App\Http\Requests\Admin\Section\FilterRequest as SectionFilterRequest;
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
     * Attach an existing slider from the library, or create one inline.
     */
    public function addSection(AddSectionRequest $request, int $page)
    {
        $data = $this->service->addSection($page, $request->validated());

        return $this->sendResponse(
            data: $data,
            message: 'Section added to page successfully'
        );
    }

    /**
     * List all sliders in the library for the "Add section" picker inside a page.
     */
    public function slidersForPage(SectionFilterRequest $request, int $page)
    {
        $data = $this->service->slidersForPage(
            $page,
            $request->validated(),
            [
                'search'   => $request->input('search'),
                'page'     => (int) $request->input('page', 1),
                'per_page' => $this->resolvePerPage($request, 50),
            ]
        );

        return $this->sendResponse(data: $data);
    }
}
