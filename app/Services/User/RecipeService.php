<?php

namespace App\Services\User;

use App\Http\Resources\Recipe\OneResource;
use App\Http\Resources\User\City\CityResource;
use App\Models\City;
use App\Models\Recipe;
use App\Services\BaseService;

class RecipeService extends BaseService
{
    public function __construct(Recipe $model)
    {
        $this->model = $model;
        $this->collection = OneResource::class;
        $this->relations = ['products'];
    }
}
