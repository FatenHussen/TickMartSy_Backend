<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recipe\AllResource;
use App\Http\Resources\Recipe\OneResource as RecipeOneResource;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\ShopProductVariant;

class RecipeController extends Controller
{
    public function index()
    {
        $recipes = Recipe::with(['items'])->get();


        return response()->json(AllResource::collection($recipes));
    }

    public function show(Recipe $recipe)
    {
        $recipe->load([
            'items.shopProductVariant.productVariant.product.category',
            'items.shopProductVariant.shop',
            'steps'
        ]);

        return new RecipeOneResource($recipe);
    }
}
