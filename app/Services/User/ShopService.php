<?php

namespace App\Services\User;

use App\Models\Shop;
use App\Services\BaseService;
use App\Http\Resources\Shop\OneResource;
use App\Http\Resources\Shop\AllResource;

class ShopService extends BaseService
{
    protected $model      = Shop::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'area',
        'vendor',
        'services',
        'productVariants',
        'productVariants.productVariant',
        'media',
    ];
    
    protected $searchableFields = ['name', 'description', 'address'];
    protected $sortableFields   = ['id', 'name', 'created_at'];

    public function getAll($filters = [], $config = [])
    {
        $specialFilters = ['type', 'lat', 'lng', 'max_distance', 'city_id', 'governorate_id'];
        $specialValues = [];
        
        foreach ($specialFilters as $filter) {
            if (isset($filters[$filter])) {
                $specialValues[$filter] = $filters[$filter];
                unset($filters[$filter]);
            }
        }

        $this->specialValues = $specialValues;

        return parent::getAll($filters, $config);
    }

    protected function applyGeographicalFilters($query)
    {
        if (!empty($this->specialValues['governorate_id'])) {
            $query->whereHas('area.city', function ($cityQuery) {
                $cityQuery->where('governorate_id', $this->specialValues['governorate_id']);
            });
        }

        if (!empty($this->specialValues['city_id'])) {
            $query->whereHas('area', function ($areaQuery) {
                $areaQuery->where('city_id', $this->specialValues['city_id']);
            });
        }
    }

    protected function applyTypeFilters($query, $type, $filters)
    {
        match ($type) {
            'nearby'       => $this->filterNearby($query, $filters),
            'offers'       => $this->filterOffers($query),
            'top_rated'    => $this->filterTopRated($query),
            'active'       => $this->filterActive($query),
            default        => null,
        };
    }

    protected function filterNearby($query, $filters)
    {
        $userLat = $filters['lat'] ?? null;
        $userLng = $filters['lng'] ?? null;
        $maxDistance = $filters['max_distance'] ?? 20; 
        
        \Log::info('filterNearby called', [
            'lat' => $userLat,
            'lng' => $userLng,
            'max_distance' => $maxDistance,
            'filters' => $filters
        ]);
        
        if (!$userLat || !$userLng) {
            \Log::info('filterNearby: Missing lat/lng, returning without filter');
            return;
        }

        $query->selectRaw("
            shops.*,
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance
        ", [$userLat, $userLng, $userLat])
        ->havingRaw('distance <= ?', [$maxDistance])
        ->orderBy('distance', 'asc');
        
        \Log::info('filterNearby: Applied distance filter and ordering');
    }

    protected function filterOffers($query)
    {
        $query->whereHas('productVariants', function ($shopVariantQuery) {
            $shopVariantQuery->whereHas('productVariant', function ($variantQuery) {
                $variantQuery->whereHas('product', function ($productQuery) {
                    $productQuery->whereNotNull('discount')
                                 ->where('discount', '>', 0);
                });
            });
        });
    }

    protected function filterTopRated($query)
    {
        $query->withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating');
    }

    protected function filterActive($query)
    {
        $query->where('is_active', true);
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        $this->applyGeographicalFilters($query);

        if (!empty($this->specialValues['type'])) {
            $this->applyTypeFilters($query, $this->specialValues['type'], $this->specialValues);
        }

        return $query;
    }
}