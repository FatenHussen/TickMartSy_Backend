<?php

namespace App\Http\Controllers\Admin\Color;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Color\FilterRequest;
use App\Http\Requests\Admin\Color\StoreRequest;
use App\Http\Requests\Admin\Color\UpdateRequest;
use App\Services\Admin\ColorService;

class ColorController extends BaseCRUDController
{
    public function __construct(ColorService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
