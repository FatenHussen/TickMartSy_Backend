<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\ShopProductVariant\AllResource;
use App\Http\Resources\Admin\ShopProductVariant\OneResource;
use App\Models\ShopProductVariant;
use App\Services\BaseService;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

class ShopProductVariantService extends BaseService
{
    protected $model = ShopProductVariant::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'productVariant.media',
        'productVariant.product.media',
        'productVariant.product.category',
        'productVariant.product.brand',
        'shop'
    ];

    protected $searchableFields = [];
    protected $sortableFields = ['id', 'price', 'cost_price', 'quantity', 'created_at'];

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

        if (array_key_exists('cost_price_min', $filters) && $filters['cost_price_min'] !== null) {
            $query->where('cost_price', '>=', $filters['cost_price_min']);
        }

        if (array_key_exists('cost_price_max', $filters) && $filters['cost_price_max'] !== null) {
            $query->where('cost_price', '<=', $filters['cost_price_max']);
        }

        return $query;
    }

    public function query(array $filters = [])
    {
        $query = ShopProductVariant::query();
        $query = $this->queryBuilder($query, $filters);
        return $query;
    }

    public function delete($id): bool
    {
        $shopVariant = ShopProductVariant::findOrFail($id);

        $activeStatuses = [
            \App\Enums\OrderStatus::PENDING->value,
            \App\Enums\OrderStatus::PREPARING->value,
            \App\Enums\OrderStatus::OUT_DELIVERY->value,
        ];

        $hasActiveOrders = \App\Models\OrderItem::where('shop_product_variant_id', $id)
            ->whereHas('order', fn($q) => $q->whereIn('status', $activeStatuses))
            ->exists();

        if ($hasActiveOrders) {
            throw new \App\Exceptions\CustomExceptionWithMessage(
                'custom.products.cannot_delete_has_active_orders',
                422
            );
        }

        // The ShopProductVariant::deleted event in the model handles
        // removing this ID from basket_items.shop_product_variant_ids JSON
        $shopVariant->delete();

        return true;
    }
}

