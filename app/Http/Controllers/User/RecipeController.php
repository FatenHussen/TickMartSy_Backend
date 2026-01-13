<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseIndexController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Recipe\AllResource;
use App\Http\Resources\Recipe\OneResource as RecipeOneResource;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\ShopProductVariant;
use App\Services\User\RecipeService;

class RecipeController extends BaseIndexController
{

    public function __construct(RecipeService $service)
    {
        $this->service = $service;
    }
}
