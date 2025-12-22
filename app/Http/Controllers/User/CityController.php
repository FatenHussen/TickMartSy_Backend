<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\GovernorateFilterRequest;
use App\Services\User\CityService;
use Illuminate\Http\Request;

class CityController extends BaseIndexController
{
    public function __construct(CityService $service)
    {
       $this->service = $service; 
       $this->filterRequest = GovernorateFilterRequest::class;
    }
}
