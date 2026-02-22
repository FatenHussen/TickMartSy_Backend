<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\ProductVariant\AllResource;
use App\Http\Resources\Admin\ProductVariant\OneResource;
use App\Models\ProductVariant;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;

class ProductVariantService extends BaseService
{
    protected $model = ProductVariant::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'product.category',
        'product.brand',
        'shopVariants.shop'
    ];

    protected $searchableFields = [];
    protected $sortableFields = ['id', 'created_at'];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query->with($this->relations);

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->whereHas('product', function (Builder $q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        // Filter by shop
        if (!empty($filters['shop_id'])) {
            $query->whereHas('shopVariants', function (Builder $q) use ($filters) {
                $q->where('shop_id', $filters['shop_id']);
            });
        }

        // Filter by product
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        // Search in product name
        if (!empty($filters['search'])) {
            $locale = app()->getLocale();
            $query->whereHas('product', function (Builder $q) use ($filters, $locale) {
                $q->where("name->{$locale}", 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query;
    }

    public function query(array $filters = [])
    {
        $query = ProductVariant::query();
        $query = $this->queryBuilder($query, $filters);
        return $query;
    }
}
