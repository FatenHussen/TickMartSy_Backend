<?php

namespace App\Services\User;

use App\Http\Resources\Recipe\AllResource;
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
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = [
            'items.shopProductVariant.productVariant.product.category',
            'items.shopProductVariant.shop',
            'steps'
        ];
    }

    public function query(array $filters = [])
    {
        $query = Recipe::query()->latest();
        return $query;
    }
}
