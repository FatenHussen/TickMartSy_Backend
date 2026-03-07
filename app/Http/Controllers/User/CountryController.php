<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Services\User\CountryService;
use Illuminate\Http\Request;

class CountryController extends BaseIndexController
{
    protected $service;

    public function __construct(CountryService $service)
    {
        $this->service = $service;
    }

    
}