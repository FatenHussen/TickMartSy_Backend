<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\ShopProductVariant\AllResource;
use App\Http\Resources\Admin\ShopProductVariant\OneResource;
use App\Models\ShopProductVariant;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;

class ShopProductVariantService extends BaseService
{
    protected $model = ShopProductVariant::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'productVariant.product.category',
        'productVariant.product.brand',
        'shop'
    ];

    protected $searchableFields = [];
    protected $sortableFields = ['id', 'price', 'quantity', 'created_at'];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query->with($this->relations);

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->whereHas('productVariant.product', function (Builder $q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        // Filter by shop
        if (!empty($filters['shop_id'])) {
            $query->where('shop_id', $filters['shop_id']);
        }

        // Filter by product
        if (!empty($filters['product_id'])) {
            $query->whereHas('productVariant', function (Builder $q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }

        // Search in product name
        if (!empty($filters['search'])) {
            $locale = app()->getLocale();
            $query->whereHas('productVariant.product', function (Builder $q) use ($filters, $locale) {
                $q->where("name->{$locale}", 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query;
    }

    public function query(array $filters = [])
    {
        $query = ShopProductVariant::query();
        $query = $this->queryBuilder($query, $filters);
        return $query;
    }
}

