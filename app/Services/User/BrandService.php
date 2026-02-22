<?php

namespace App\Services\User;

use App\Http\Resources\Brand\OneResource;
use App\Http\Resources\Brand\AllResource;
use App\Models\Brand;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;

class BrandService extends BaseService
{
    public function __construct(Brand $model)
    {
        $this->model = $model;
        $this->collection = AllResource::class;
        $this->resource = OneResource::class;
        $this->searchableFields = ['name'];
        $this->sortableFields = ['id', 'created_at'];
    }

    protected function applyTypeFilters($query, $type)
    {
        match ($type) {
            'new' => $this->filterNew($query),
            'top_rated' => $this->filterTopRated($query),
            'most_popular' => $this->filterMostPopular($query),
            default => null,
        };
    }

    protected function filterNew($query)
    {
        $query->orderBy('created_at', 'desc');
    }

    protected function filterTopRated($query)
    {
        $query->withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating');
    }

    protected function filterMostPopular($query)
    {
        // Most popular based on number of products sold
        $query->withCount(['products as sold_count' => function ($q) {
            $q->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->join('shop_product_variants', 'product_variants.id', '=', 'shop_product_variants.product_variant_id')
                ->join('order_items', 'shop_product_variants.id', '=', 'order_items.shop_product_variant_id')
                ->selectRaw('COALESCE(SUM(order_items.quantity), 0)');
        }])->orderByDesc('sold_count');
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Search filter
        if (!empty($filters['search'])) {
            $locale = app()->getLocale();
            $query->where(function (Builder $q) use ($filters, $locale) {
                $q->where("name->{$locale}", 'like', '%' . $filters['search'] . '%');
            });
        }

        // Type filter
        if (!empty($filters['type'])) {
            $this->applyTypeFilters($query, $filters['type']);
        }

        return $query;
    }

    public function query(array $filters = [])
    {
        $query = Brand::query();
        $query = $this->queryBuilder($query, $filters);

        // Default ordering if no type filter
        if (empty($filters['type'])) {
            $query->latest();
        }

        return $query;
    }
}
