<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class BaseIndexController extends Controller
{
    protected $service;
    protected $filterRequest;

    public function index(Request $request)
    {
        $filters = $this->filterRequest ? app($this->filterRequest)->validated() : [];

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


    public function get_one($id)
    {
        $res = $this->service->getOne($id);
        return $this->sendResponse($res);
    }
}
