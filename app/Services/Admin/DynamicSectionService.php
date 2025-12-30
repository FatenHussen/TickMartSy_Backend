<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Support\Collection;

class DynamicSectionService
{

    public static function fetch(string $apiSource, array $filters = [])
    {
        switch ($apiSource) {
            case 'trending_products':
                return self::trendingProducts($filters);

            case 'trending_clothes':
                return self::trendingClothes($filters);

            case 'trending_recipes':
                return self::trendingRecipes($filters);

            default:
                return collect();
        }
    }


    protected static function trendingProducts(array $filters = [])
    {
        // $query = Product::query()->orderBy('sold_count', 'desc');

        // if (!empty($filters['category_id'])) {
        //     $query->where('category_id', $filters['category_id']);
        // }
        // if (!empty($filters['price_max'])) {
        //     $query->where('price', '<=', $filters['price_max']);
        // }
        // if (!empty($filters['discount_max'])) {
        //     $query->where('discount', '<=', $filters['discount_max']);
        // }
        // if (!empty($filters['brand'])) {
        //     $query->whereIn('brand', (array)$filters['brand']);
        // }

        // return $query->take(20)->get();
    }


    protected static function trendingClothes(array $filters = [])
    {
        // $query = Product::query()
        //     ->where('category', 'clothes')
        //     ->orderBy('sold_count', 'desc');

        // if (!empty($filters['price_max'])) {
        //     $query->where('price', '<=', $filters['price_max']);
        // }
        // if (!empty($filters['brand'])) {
        //     $query->whereIn('brand', (array)$filters['brand']);
        // }

        // return $query->take(20)->get();
    }


    protected static function trendingRecipes(array $filters = [])
    {
        // $query = Recipe::query()->orderBy('views', 'desc');

        // if (!empty($filters['category_id'])) {
        //     $query->where('category_id', $filters['category_id']);
        // }
        // if (!empty($filters['keyword'])) {
        //     $query->where('name', 'like', '%' . $filters['keyword'] . '%');
        // }

        // return $query->take(20)->get();
    }
}
