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
        $this->applyTypeFilters($query, $filters);

        return $query;
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

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->whereHas('productVariants.productVariant.product', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
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

        $distance = $filters['max_distance'] ?? 20;

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

    public function query(array $filters = [])
    {
        $query = Shop::query();
        $query =  $this->queryBuilder($query, $filters);
        return $query;
    }
}
