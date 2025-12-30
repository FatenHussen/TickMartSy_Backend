<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class BaseIndexController extends Controller
{
    protected $service;
    protected $filterRequest;

    public function index(
        Request $request
    ) {
        $filters = $this->filterRequest ? app($this->filterRequest)->validated() : [];
        $res = $this->service->getAll($filters);
        return $this->sendResponse($res);
    }

    public function get_one($id)
    {
        $res = $this->service->getOne($id);
        return $this->sendResponse($res);
    }
}
