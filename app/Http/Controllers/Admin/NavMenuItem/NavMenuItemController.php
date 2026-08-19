<?php

namespace App\Http\Controllers\Admin\NavMenuItem;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\NavMenuItem\SortRequest;
use App\Http\Requests\Admin\NavMenuItem\StoreRequest;
use App\Http\Requests\Admin\NavMenuItem\UpdateRequest;
use App\Services\Admin\NavMenuItemService;
use Illuminate\Http\Request;

class NavMenuItemController extends BaseCRUDController
{
    public function __construct(NavMenuItemService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    public function index(Request $request)
    {
        // Default to the visual menu order unless the dashboard asks otherwise.
        $config = [
            'search'    => $request->input('search'),
            'sortField' => $request->input('sort_field') ?? 'order',
            'sortOrder' => $request->input('sort_order') ?? 'asc',
            'page'      => (int) $request->input('page', 1),
            'per_page'  => $this->resolvePerPage($request, 50),
        ];

        return $this->sendResponse(data: $this->service->getAll([], $config));
    }

    public function sort(SortRequest $request)
    {
        $updated = $this->service->reorder($request->validated('ordered_ids'));

        return $this->sendResponse(
            data: ['updated_count' => $updated],
            message: 'Menu order updated successfully'
        );
    }
}
