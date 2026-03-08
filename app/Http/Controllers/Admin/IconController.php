<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Icon\StoreIconRequest;
use App\Http\Requests\Admin\Icon\UpdateIconRequest;
use App\Services\Admin\IconService;

class IconController extends BaseCRUDController
{
    public function __construct(IconService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreIconRequest::class;
        $this->updateRequest = UpdateIconRequest::class;
    }
}
