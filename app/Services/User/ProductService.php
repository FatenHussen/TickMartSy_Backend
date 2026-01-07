<?php 

namespace App\Services\User;

use App\Models\Product;
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
    public function query(array $filters)
    {
        $query = Product::query()
            ->with($this->relations)
            ->latest();

        $this->applyBasicFilters($query, $filters);
        $this->applyTypeFilters($query, $filters);

        return $query;
    }
    protected function applyBasicFilters($query, $filters)
    {
        // if (!empty($filters['category_id'])) {
        //     $query->where('category_id', $filters['category_id']);
        // }

        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        if (!empty($filters['country'])) {
            $query->where('country->' . app()->getLocale(), 'like', '%' . $filters['country'] . '%');
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name->' . app()->getLocale(), 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description->' . app()->getLocale(), 'like', '%' . $filters['search'] . '%');
            });
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
        $query;
        // $query->withCount('items')
        //     ->orderBy('items_count', 'desc');
    }
    protected function filterTopRated($query)
    {
        $query;
        // $query->withAvg('reviews', 'rating')
        //     ->orderByDesc('reviews_avg_rating');
    }
    protected function filterOffers($query)
    {
        $query;
        // $query->whereNotNull('price_after_discount')
        //     ->whereColumn('price_after_discount', '<', 'price');
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
}