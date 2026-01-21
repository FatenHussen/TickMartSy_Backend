<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CityFilterRequest;
use App\Http\Requests\User\GovernorateFilterRequest;
use App\Services\Admin\AreaService;
use App\Services\User\CityService;
use Illuminate\Http\Request;

class AreaController extends BaseIndexController
{
    public function __construct(AreaService $service)
    {
        $this->service = $service;
        $this->filterRequest = CityFilterRequest::class;
    }
}
