<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\User\Recipe\FilterRequest;
use App\Services\User\RecipeService;

class RecipeController extends BaseIndexController
{
    public function __construct(RecipeService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }
}
