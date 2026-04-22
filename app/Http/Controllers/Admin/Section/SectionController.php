<?php

namespace App\Http\Controllers\Admin\Section;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Section\DisplayTypesRequest;
use App\Http\Resources\DisplayType\OneResource;
use App\Models\Page;
use App\Services\Admin\SectionService;

class SectionController extends BaseCRUDController
{
    public function pages()
    {
        return $this->sendResponse(data: Page::all());
    }
    public function sectionItemTypes()
    {
        return $this->sendResponse(data: config('section_items'));
    }
    public function displayTypes(DisplayTypesRequest $request, SectionService $sectionService)
    {
        $data = $sectionService->displayTypes(
            manualModel: $request->string('manual_model')->toString(),
            pageId: $request->integer('page_id')
        );

        return $this->sendResponse(data: OneResource::collection($data));
    }
}
