<?php

namespace App\Services\User;

use Illuminate\Database\Eloquent\Builder;

use App\Models\Product;
use App\Models\Category;
use App\Services\BaseService;
use App\Http\Resources\Product\OneResource;
use App\Http\Resources\Product\AllResource;

class ProductService extends BaseService
{
    protected $model      = Product::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
        'variants',
        'variants.shopVariants',
        'categoryDetails.categoryDetail',
        'extraDetails',
        'variants.shopVariants.shop',
    ];
    protected $searchableFields = ['name', 'description', 'country'];
    protected $sortableFields   = ['id', 'price', 'created_at', 'name'];
    
    /**
     * Recursively collect all descendant category IDs
     */
    private function collectDescendantIds($categories, &$categoryIds)
    {
        foreach ($categories as $category) {
            $categoryIds->push($category->id);
            if ($category->descendants && $category->descendants->count() > 0) {
                $this->collectDescendantIds($category->descendants, $categoryIds);
            }
        }
    }
    protected function applyTypeFilters($query, $filters)
    {
        if (empty($filters['type'])) {
            return;
        }

        match ($filters['type']) {
            'new'          => $this->filterNew($query),
            'trend'        => $this->filterTrend($query),
            'top_rated'    => $this->filterTopRated($query),
            'offers'       => $this->filterOffers($query),
            'recommended'  => $this->filterRecommended($query),
            'for_you'      => $this->filterForYou($query),
            'search_based' => $this->filterSearchBased($query, $filters),
            default        => null,
        };
    }
    protected function filterNew($query)
    {
        $query->orderBy('created_at', 'desc');
    }
    protected function filterTrend($query)
    {
        $query->whereHas('variants', function ($q) {
            $q->where('is_trend', true);
        })
        ->withCount(['variants as sold_count' => function ($q) {
            $q->join('shop_product_variants', 'product_variants.id', '=', 'shop_product_variants.product_variant_id')
              ->join('order_items', 'shop_product_variants.id', '=', 'order_items.shop_product_variant_id')
              ->join('orders', 'order_items.order_id', '=', 'orders.id')
              ->where('orders.order_status', 'completed')
              ->selectRaw('COALESCE(SUM(order_items.quantity), 0)');
        }])
        ->orderByDesc('sold_count');
    }
    protected function filterTopRated($query)
    {
        $query->withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating');
    }
    protected function filterOffers($query)
    {
        $query->whereNotNull('discount')
            ->where('discount', '>', 0)
            ->orderByDesc('discount');
    }
    protected function filterRecommended($query)
    {
        $user = auth('user')->user();

        if (!$user) return;
        // $categoryIds = $user
        // ->orders()
        //     ->with('items.product')
        //     ->get()
        //     ->pluck('items.*.product.category_id')
        //     ->flatten()
        //     ->unique()
        //     ->toArray();
        // $query->whereIn('category_id', $categoryIds);
        $query;
    }
    protected function filterForYou($query)
    {
        $query;
        // $user = auth('user')->user();

        // if (!$user) return;

        // $query->whereHas('variants.shopVariants.orders', function ($q) use ($user) {
        //     $q->where('user_id', $user->id);
        // })->orWhere('is_instant_delivery', 1);
    }
    protected function filterSearchBased($query, $filters)
    {
        if (empty($filters['search'])) return;

        $query->where(function ($q) use ($filters) {
            $q->where('name->' . app()->getLocale(), 'like', '%' . $filters['search'] . '%')
                ->orWhere('description->' . app()->getLocale(), 'like', '%' . $filters['search'] . '%')
                ->orWhere('country->' . app()->getLocale(), 'like', '%' . $filters['search'] . '%');
        });
    }
    public function queryBuilder($query, $filters = [], $config = [])
    {

        $query->with([
            'category',
            'variants',
            'variants.shopVariants',
            'categoryDetails.categoryDetail',
            'extraDetails',
            'variants.shopVariants.shop',
            'media',
        ]);

        if (!empty($filters['category_id'])) {
            $category = Category::find($filters['category_id']);
            if ($category) {
                // Get all descendant category IDs
                $categoryIds = collect([$category->id]);
                $descendants = $category->descendants()->get();
                
                // Recursively collect all descendant IDs
                $this->collectDescendantIds($descendants, $categoryIds);
                
                $query->whereIn('category_id', $categoryIds->toArray());
            }
        }
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }
        if (!empty($filters['shop_id'])) {
            $query->whereHas('variants.shopVariants', function (Builder $q) use ($filters) {
                $q->where('shop_id', $filters['shop_id']);
            });
        }

        if (!empty($filters['price_min'])) {
            $query->whereHas('variants.shopVariants', function (Builder $q) use ($filters) {
                $q->where('price', '>=', $filters['price_min']);
            });
        }

        if (!empty($filters['price_max'])) {
            $query->whereHas('variants.shopVariants', function (Builder $q) use ($filters) {
                $q->where('price', '<=', $filters['price_max']);
            });
        }

        if (!empty($filters['search'])) {
            $locale = app()->getLocale();
            $query->where(function (Builder $q) use ($filters, $locale) {
                $q->where("name->{$locale}", 'like', '%' . $filters['search'] . '%')
                    ->orWhere("description->{$locale}", 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['country'])) {
            $query->where('country->' . app()->getLocale(), 'like', '%' . $filters['country'] . '%');
        }

        if (!empty($filters['type'])) {
            $this->applyTypeFilters($query, $filters['type'], $filters);
        }

        return $query;
    }
}
