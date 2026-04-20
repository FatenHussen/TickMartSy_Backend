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
            'favorites',
            'media'
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
        return $query = $this->queryBuilder($query, $filters);
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

    //     if (!empty($filters['sort_by'])) {
    //         $this->applySortBy($query, $filters['sort_by']);
    //     }

    //     return $query;
    // }
    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query->with($this->relations);

        // نحسب أقل سعر مكون داخل الوصفة
        $query->withMin('variants', 'price');

        $query->where('is_active', true);
        /* ================= SEARCH ================= */

        if (!empty($filters['search'])) {

            $search = strtolower($filters['search']);
            $locale = app()->getLocale();

            $query->where(function (Builder $q) use ($search, $locale) {

                $q->whereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.{$locale}'))) LIKE ?",
                    ["%{$search}%"]
                )->orWhereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.{$locale}'))) LIKE ?",
                    ["%{$search}%"]
                );
            });
        }

        /* ================= DISCOUNT FILTER ================= */

        if (!empty($filters['discount_min'])) {
            $query->where('discount', '>=', $filters['discount_min']);
        }

        if (!empty($filters['discount_max'])) {
            $query->where('discount', '<=', $filters['discount_max']);
        }

        // Filter recipes with discount only
        if (isset($filters['has_discount']) && filter_var($filters['has_discount'], FILTER_VALIDATE_BOOLEAN)) {
            $query->where('discount', '>', 0);
        }

        /* ================= RATING FILTER ================= */

        if (!empty($filters['rating_min'])) {
            $query->where('rating', '>=', $filters['rating_min']);
        }

        if (!empty($filters['rating_max'])) {
            $query->where('rating', '<=', $filters['rating_max']);
        }

        /* ================= SERVES FILTER ================= */

        if (!empty($filters['serves'])) {
            // البحث الجزئي في حقل serves (مثال: "2-4" يطابق "2-4" أو يحتوي على "2")
            $query->where('serves', 'LIKE', '%' . $filters['serves'] . '%');
        }

        /* ================= PREPARE TIME FILTER ================= */

        if (!empty($filters['prepare_time'])) {
            // البحث الجزئي في حقل prepare_time (مثال: "25" يطابق "25" أو "25 minutes")
            $query->where('prepare_time', 'LIKE', '%' . $filters['prepare_time'] . '%');
        }

        /* ================= TYPE FILTER ================= */

        if (!empty($filters['type'])) {
            match ($filters['type']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'popular' => $query->orderBy('orders_count', 'desc'),
                'top_rated' => $query->where('rating', '>=', 4)->orderBy('rating', 'desc'),
                'on_sale' => $query->where('discount', '>', 0)->orderBy('discount', 'desc'),
                default => null,
            };
        }

        /* ================= SORT ================= */

        if (!empty($filters['sort_by'])) {
            $this->applySortBy($query, $filters['sort_by']);
        } elseif (empty($filters['type'])) {
            // إذا لم يكن هناك type أو sort_by، استخدم الترتيب الافتراضي
            $query->latest();
        }

        /* ================= FAVORITES ================= */
        if (
            auth('user')->check() &&
            method_exists($query->getModel(), 'favorites')
        ) {
            $query->withExists([
                'favorites as is_favorite' => function ($q) {
                    $q->where('user_id', auth('user')->id());
                }
            ]);
        }

        return $query;
    }
    protected function applySortBy($query, string $sortBy): void
    {
        $query->reorder();

        match ($sortBy) {

            'newest' => $query->orderBy('created_at', 'desc'),

            'oldest' => $query->orderBy('created_at', 'asc'),

            'price_desc' => $query->orderBy('variants_min_price', 'desc'),

            'price_asc' => $query->orderBy('variants_min_price', 'asc'),

            'rating_desc' => $query->orderBy('rating', 'desc'),

            'rating_asc' => $query->orderBy('rating', 'asc'),

            'popular' => $query->orderBy('orders_count', 'desc'),

            'discount_desc' => $query->orderBy('discount', 'desc'),

            default => null,
        };
    }
}
