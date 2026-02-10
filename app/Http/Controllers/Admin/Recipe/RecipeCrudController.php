<?php

namespace App\Http\Controllers\Admin\Recipe;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Recipe\StoreRequest;
use App\Http\Requests\Admin\Recipe\UpdateRequest;
use App\Services\Admin\RecipeService;

class RecipeCrudController extends BaseCRUDController
{

    public function __construct(
        RecipeService $service
    ) {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
}
