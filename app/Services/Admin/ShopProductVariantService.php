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
    protected $sortableFields = ['id', 'cost_price', 'created_at'];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query->with($this->relations);

        $categoryIds = array_values(array_unique(array_filter(array_merge(
            $this->normalizeIdInput($filters['category_id'] ?? null),
            $this->normalizeIdInput($filters['category_ids'] ?? null)
        ))));

        // Filter by category
        if (!empty($categoryIds)) {
            $query->whereHas('productVariant.product', function (Builder $q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
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

        if (!empty($filters['product_number'])) {
            $query->whereHas('productVariant.product', function (Builder $q) use ($filters) {
                $q->where('product_number', $filters['product_number']);
            });
        }

        // Search in product name
        if (!empty($filters['search'])) {
            $locale = app()->getLocale();
            $search = trim((string) $filters['search']);

            $query->whereHas('productVariant.product', function (Builder $q) use ($search, $locale) {
                $q->where("name->{$locale}", 'like', '%' . $search . '%')
                    ->orWhere('product_number', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');

                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
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

    private function normalizeIdInput(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return collect($value)
                ->flatMap(fn($item) => $this->normalizeIdInput($item))
                ->values()
                ->all();
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeIdInput($decoded);
            }

            if (str_contains($value, ',')) {
                return collect(explode(',', $value))
                    ->map(fn($item) => trim($item))
                    ->flatMap(fn($item) => $this->normalizeIdInput($item))
                    ->values()
                    ->all();
            }
        }

        if (is_numeric($value)) {
            return [(int) $value];
        }

        return [];
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
