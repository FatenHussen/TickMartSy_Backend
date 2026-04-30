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
        'badges',
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
        $this->applyShopClassificationFilters($query, $filters);
        $this->applyRestaurantFilters($query, $filters);
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

    protected function applyRestaurantFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['pricing_tier'])) {
            $query->where('pricing_tier', $filters['pricing_tier']);
        }

        if (array_key_exists('is_open_now', $filters) && $filters['is_open_now'] !== null) {
            $this->filterByOpenStatus($query, (bool) $filters['is_open_now']);
        }
    }

    protected function applyShopClassificationFilters(Builder $query, array $filters): void
    {
        if (array_key_exists('is_service_provider', $filters) && $filters['is_service_provider'] !== null) {
            $query->where('is_service_provider', (bool) $filters['is_service_provider']);
        }

        if (array_key_exists('is_restaurant', $filters) && $filters['is_restaurant'] !== null) {
            $query->where('is_restaurant', (bool) $filters['is_restaurant']);
        }

        if (empty($filters['shop_type'])) {
            return;
        }

        match ($filters['shop_type']) {
            'restaurant' => $query->where('is_restaurant', true),
            'service_provider' => $query->where('is_service_provider', true),
            'store' => $query
                ->where('is_restaurant', false)
                ->where('is_service_provider', false),
            default => null,
        };
    }

    /* =========================
     |  GEO FILTERS
     ========================= */
    protected function applyGeographicalFilters(Builder $query, array $filters)
    {
        $governorateId = $this->normalizeFilterId($filters['governorate_id'] ?? null);
        if ($governorateId !== null) {
            $query->whereHas('area.city', function ($q) use ($governorateId) {
                $q->where('governorate_id', $governorateId);
            });
        }

        $cityId = $this->normalizeFilterId($filters['city_id'] ?? null);
        if ($cityId !== null) {
            $query->whereHas('area', function ($q) use ($cityId) {
                $q->where('city_id', $cityId);
            });
        }

        // Product-based filters - shops that have products matching category/brand conditions
        $categoryId = $this->normalizeFilterId($filters['category_id'] ?? null);
        $brandId = $this->normalizeFilterId($filters['brand_id'] ?? null);

        if ($categoryId !== null || $brandId !== null) {
            $query->whereHas('productVariants.productVariant.product', function ($q) use ($categoryId, $brandId) {
                if ($categoryId !== null) {
                    $q->where('category_id', $categoryId);
                }

                if ($brandId !== null) {
                    $q->where('brand_id', $brandId);
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
            'nearby', 'near_me' => $this->filterNearby($query, $filters),
            'offers'    => $this->filterOffers($query),
            'top_rated', 'most_rated' => $this->filterTopRated($query),
            'active'    => $this->filterActive($query),
            'free_delivery' => $this->filterFreeDelivery($query),
            'open' => $this->filterByOpenStatus($query, true),
            'close' => $this->filterByOpenStatus($query, false),
            'newest' => $query->orderBy('created_at', 'desc'),
            'zone' => $this->filterZone($query, $filters),
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
            'most_rated', 'rating_desc' => $query->withAvg('ratings', 'rating')->orderByDesc('ratings_avg_rating'),
            'rating_asc' => $query->withAvg('ratings', 'rating')->orderBy('ratings_avg_rating'),
            'near_me' => $this->filterNearby($query, request()->all()),
            default => null,
        };
    }

    protected function filterZone(Builder $query, array $filters): void
    {
        if (!empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }
    }

    protected function filterByOpenStatus(Builder $query, bool $isOpen): void
    {
        $day = strtolower(now()->englishDayOfWeek);
        $now = now()->format('H:i');

        if ($isOpen) {
            $query
                ->whereRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(working_hours, '$.{$day}.closed')), 'false') != 'true'")
                ->whereRaw("TIME(JSON_UNQUOTE(JSON_EXTRACT(working_hours, '$.{$day}.open'))) <= ?", [$now])
                ->whereRaw("TIME(JSON_UNQUOTE(JSON_EXTRACT(working_hours, '$.{$day}.close'))) >= ?", [$now]);
            return;
        }

        $query->where(function (Builder $q) use ($day, $now) {
            $q->whereRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(working_hours, '$.{$day}.closed')), 'false') = 'true'")
                ->orWhereRaw("TIME(JSON_UNQUOTE(JSON_EXTRACT(working_hours, '$.{$day}.open'))) > ?", [$now])
                ->orWhereRaw("TIME(JSON_UNQUOTE(JSON_EXTRACT(working_hours, '$.{$day}.close'))) < ?", [$now]);
        });
    }

    public function query(array $filters = [])
    {
        $query = Shop::query();
        $query =  $this->queryBuilder($query, $filters);
        return $query;
    }

    private function normalizeFilterId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
