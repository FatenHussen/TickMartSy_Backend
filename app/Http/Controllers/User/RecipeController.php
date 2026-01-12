<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recipe\AllResource;
use App\Http\Resources\Recipe\OneResource as RecipeOneResource;
use App\Models\Category;
use App\Models\Recipe;

class RecipeController extends Controller
{
    public function index()
    {
        $recipes = Recipe::with(['items'])->get();


        return response()->json(AllResource::collection($recipes));
    }

    public function show(Recipe $recipe)
    {
        // Load relations الأساسية
        $recipe->load([
            'items.shopProductVariant.productVariant.product',
            'items.shopProductVariant.shop'
        ]);

        $recipeData = RecipeOneResource::make($recipe)->toArray(request());

        // // 1. جلب كل الفئات الفرعية للـ level المختار
        // $parentCategory = Category::find($recipe->switchable_category_level);
        // $categoryIds = array_merge(
        //     [$parentCategory->id],
        //     $parentCategory->descendants()->pluck('id')->toArray()
        // );

        // // 2. جلب المنتجات البديلة في كل الفروع بنفس الفئة
        // $alternativeVariants = ShopProductVariant::with('productVariant.product', 'shop')
        //     ->whereHas('productVariant.product', function ($q) use ($categoryIds) {
        //         $q->whereIn('category_id', $categoryIds);
        //     })
        //     ->where('quantity', '>', 0)
        //     ->get();

        // // 3. قسمهم
        // $sameShop = $alternativeVariants->where('shop_id', $currentShopId)->values();
        // $otherShops = $alternativeVariants->where('shop_id', '!=', $currentShopId)->values();

        return response()->json($recipeData);
    }
}
