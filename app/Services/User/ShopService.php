<?php

namespace App\Services\User;

use App\Models\Shop;
use App\Services\BaseService;
use App\Http\Resources\Shop\OneResource;
use App\Http\Resources\Shop\AllResource;
use Illuminate\Database\Eloquent\Builder;

class ShopService extends BaseService
{
    protected $model      = Shop::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'area.city',
        'vendor',
        'services',
        'productVariants.productVariant.product',
        'media',
        'favorites'
    ];

    protected $searchableFields = ['name', 'description', 'address'];
    protected $sortableFields   = ['id', 'name', 'created_at'];

    /* =========================
     |  QUERY BUILDER
     ========================= */
    public function queryBuilder($query, $filters = [], $config = [])
    {
        // $query = parent::queryBuilder($query, $filters, $config);

        $this->applyGeographicalFilters($query, $filters);
        $this->applyServiceProviderFilter($query, $filters);
        $this->applyTypeFilters($query, $filters);
        if (!empty($filters['sort_by'])) {
            $this->applySortBy($query, $filters['sort_by']);
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

        return $query->where('is_active', true);
    }

    protected function applyServiceProviderFilter(Builder $query, array $filters): void
    {
        if (!array_key_exists('is_service_provider', $filters) || $filters['is_service_provider'] === null) {
            return;
        }

        $query->where('is_service_provider', (bool) $filters['is_service_provider']);
    }

    /* =========================
     |  GEO FILTERS
     ========================= */
    protected function applyGeographicalFilters(Builder $query, array $filters)
    {
        if (!empty($filters['governorate_id'])) {
            $query->whereHas('area.city', function ($q) use ($filters) {
                $q->where('governorate_id', $filters['governorate_id']);
            });
        }

        if (!empty($filters['city_id'])) {
            $query->whereHas('area', function ($q) use ($filters) {
                $q->where('city_id', $filters['city_id']);
            });
        }

        // Product-based filters - shops that have products matching category/brand conditions
        if (!empty($filters['category_id']) || !empty($filters['brand_id'])) {
            $query->whereHas('productVariants.productVariant.product', function ($q) use ($filters) {
                if (!empty($filters['category_id'])) {
                    $q->where('category_id', $filters['category_id']);
                }

                if (!empty($filters['brand_id'])) {
                    $q->where('brand_id', $filters['brand_id']);
                }
            });
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $locale = app()->getLocale();
            $query->where(function ($q) use ($search, $locale) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.{$locale}'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.{$locale}'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address, '$.{$locale}'))) LIKE ?", ["%{$search}%"]);
            });
        }
    }

    /* =========================
     |  TYPE FILTERS
     ========================= */
    protected function applyTypeFilters(Builder $query, array $filters)
    {
        if (empty($filters['type'])) {
            return;
        }

        match ($filters['type']) {
            'nearby'    => $this->filterNearby($query, $filters),
            'offers'    => $this->filterOffers($query),
            'top_rated' => $this->filterTopRated($query),
            'active'    => $this->filterActive($query),
            'free_delivery' => $this->filterFreeDelivery($query),
            default     => null,
        };
    }

    /* =========================
     |  TYPE IMPLEMENTATIONS
     ========================= */
    protected function filterNearby(Builder $query, array $filters)
    {
        if (empty($filters['lat']) || empty($filters['lng'])) {
            return;
        }

        $distance = $filters['max_distance'] ?? 100;

        $query->selectRaw(
            "shops.*,
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(lat)) *
                cos(radians(lng) - radians(?)) +
                sin(radians(?)) *
                sin(radians(lat))
            )) AS distance",
            [$filters['lat'], $filters['lng'], $filters['lat']]
        )
            ->having('distance', '<=', $distance)
            ->orderBy('distance');
    }

    protected function filterOffers(Builder $query)
    {
        $query->whereHas('productVariants.productVariant.product', function ($q) {
            $q->whereNotNull('discount')
                ->where('discount', '>', 0);
        });
    }

    protected function filterTopRated(Builder $query)
    {
        $query->withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating');
    }

    protected function filterActive(Builder $query)
    {
        $query->where('is_active', true);
    }

    protected function filterFreeDelivery(Builder $query)
    {
        $query->where('is_free_delivery', true);
    }

    protected function applySortBy(Builder $query, string $sortBy): void
    {
        $query->reorder();

        match ($sortBy) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => null,
        };
    }

    public function query(array $filters = [])
    {
        $query = Shop::query();
        $query =  $this->queryBuilder($query, $filters);
        return $query->where('is_active', true);
    }
}
