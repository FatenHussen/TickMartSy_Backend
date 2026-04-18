<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Category\SortRequest;
use App\Http\Requests\Admin\Category\FilterRequest;
use App\Http\Requests\Admin\Category\StoreRequest;
use App\Http\Requests\Admin\Category\UpdateRequest;
use App\Services\Admin\CategoryService;

class CategoryController extends BaseCRUDController
{
    public function __construct(CategoryService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    public function sort(SortRequest $request)
    {
        $updated = $this->service->reorder(
            $request->validated('ordered_ids'),
            $request->validated('parent_id')
        );

        return $this->sendResponse(
            data: ['updated_count' => $updated],
            message: 'Category order updated successfully'
        );
    }
}
