<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseIndexController;
use App\Services\GovernorateService;

class GovernorateController extends BaseIndexController
{
    public function __construct(GovernorateService $service)
    {
       $this->service = $service; 
    }
}
