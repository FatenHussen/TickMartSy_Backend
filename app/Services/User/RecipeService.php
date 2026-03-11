<?php

namespace App\Services\User;

use App\Http\Resources\Recipe\AllResource;
use App\Http\Resources\Recipe\OneResource;
use App\Models\Recipe;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;

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
            'steps',
            'favorites'
        ];
        $this->searchableFields = ['name', 'description'];
        $this->sortableFields = ['id', 'discount', 'rating', 'orders_count', 'created_at'];
    }

    // public function queryBuilder($query, $filters = [], $config = [])
    // {
    //     $query->with($this->relations);

    //     // Search filter
    //     if (!empty($filters['search'])) {
    //         $search = strtolower($filters['search']);
    //         $locale = app()->getLocale();
    //         $query->where(function (Builder $q) use ($search, $locale) {
    //             $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.{$locale}'))) LIKE ?", ["%{$search}%"])
    //                 ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.{$locale}'))) LIKE ?", ["%{$search}%"]);
    //         });
    //     }

    //     // Discount filter
    //     if (!empty($filters['discount_min']) || !empty($filters['discount_max'])) {
    //         if (!empty($filters['discount_min'])) {
    //             $query->where('discount', '>=', $filters['discount_min']);
    //         }
    //         if (!empty($filters['discount_max'])) {
    //             $query->where('discount', '<=', $filters['discount_max']);
    //         }
    //     }

    //     // Serves filter
    //     if (!empty($filters['serves'])) {
    //         $query->where('serves', $filters['serves']);
    //     }

    //     // Prepare time filter
    //     if (!empty($filters['prepare_time'])) {
    //         $query->where('prepare_time', $filters['prepare_time']);
    //     }

    //     return $query;
    // }

    public function query(array $filters = [])
    {
        $query = Recipe::query()->latest();
        $query = $this->queryBuilder($query, $filters);
        return $query;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query->with($this->relations);

        // نحسب أقل سعر داخل الوصفة
        $query->withMin('items.shopProductVariant', 'price');

        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $locale = app()->getLocale();

            $query->where(function (Builder $q) use ($search, $locale) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.{$locale}'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.{$locale}'))) LIKE ?", ["%{$search}%"]);
            });
        }

        if (!empty($filters['discount_min'])) {
            $query->where('discount', '>=', $filters['discount_min']);
        }

        if (!empty($filters['discount_max'])) {
            $query->where('discount', '<=', $filters['discount_max']);
        }

        if (!empty($filters['serves'])) {
            $query->where('serves', $filters['serves']);
        }

        if (!empty($filters['prepare_time'])) {
            $query->where('prepare_time', $filters['prepare_time']);
        }

        if (!empty($filters['sort_by'])) {
            $this->applySortBy($query, $filters['sort_by']);
        }

        return $query;
    }

    protected function applySortBy($query, string $sortBy): void
    {
        $query->reorder();

        match ($sortBy) {
            'newest'     => $query->orderBy('created_at', 'desc'),
            'oldest'     => $query->orderBy('created_at', 'asc'),
            'price_desc' => $query->orderBy('items_shop_product_variant_min_price', 'desc'),
            'price_asc'  => $query->orderBy('items_shop_product_variant_min_price', 'asc'),
            default      => null,
        };
    }
}
