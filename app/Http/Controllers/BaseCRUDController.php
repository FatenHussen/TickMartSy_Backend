<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BaseCRUDController extends Controller
{
    protected $service;
    protected $filterRequest;
    protected $createRequest;
    protected $updateRequest;
    protected $searchableFields;
    protected $sortableFields;

    public function index(Request $request)
    {
        $filters = $this->resolveValidatedFilters($request, $this->filterRequest);

        $config = [
            'search'     => $request->input('search'),
            'sortField'  => $request->input('sort_field') ?? 'id',
            'sortOrder'  => $request->input('sort_order') ?? 'desc',
            'page'       => (int) $request->input('page', 1),
            'per_page'    => $this->resolvePerPage($request),
        ];

        $res = $this->service->getAll($filters, $config);
        return $this->sendResponse(data: $res);
    }
    public function show($id)
    {
        $res = $this->service->getOne($id);
        return $this->sendResponse(data: $res);
    }
    public function store(Request $request)
    {
        $data = $this->resolveValidatedFilters($request, $this->createRequest);
        Log::info($data);
        $res = $this->service->create($data);
        return $this->sendResponse(data: $res);
    }
    public function update(Request $request, $id)
    {
        $data = $this->resolveValidatedFilters($request, $this->updateRequest);
        $res = $this->service->update($id, $data);
        return $this->sendResponse(data: $res);
    }

    public function destroy($id)
    {
        $res = $this->service->delete($id);
        return $this->sendResponse(data: $res);
    }
}
