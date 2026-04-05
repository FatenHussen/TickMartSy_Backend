<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\ProductVariant\AllResource;
use App\Http\Resources\Admin\ProductVariant\OneResource;
use App\Models\ProductVariant;
use App\Services\Base\MediaService;
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
        'shopVariants.shop',
        'media',
    ];

    protected $searchableFields = [];
    protected $sortableFields = ['id', 'created_at'];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query->with($this->relations);

        // Remove fields that are not direct columns
        unset($filters['attributes_values_ids']);

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

    public function update($id, array $data)
    {
        $variant = ProductVariant::findOrFail($id);

        // Handle image deletions
        if (isset($data['existing_images_ids'])) {
            $existingIds = $data['existing_images_ids'];
            $mediaService = new MediaService();

            $variant->media()->whereNotIn('id', $existingIds)->each(
                fn($media) => $mediaService->delete($media)
            );

            unset($data['existing_images_ids']);
        }

        // Handle new image uploads
        if (!empty($data['images'])) {
            $mediaService = new MediaService();
            foreach ($data['images'] as $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $mediaService->upload($variant, $file, 'variant_images');
                }
            }
            unset($data['images']);
        }

        $variant->update($data);
        $variant->refresh();

        return new ($this->resource)($variant);
    }

    public function delete($id): bool
    {
        $variant = ProductVariant::with('shopVariants')->findOrFail($id);

        $activeStatuses = [
            \App\Enums\OrderStatus::PENDING->value,
            \App\Enums\OrderStatus::PREPARING->value,
            \App\Enums\OrderStatus::OUT_DELIVERY->value,
        ];

        $shopVariantIds = $variant->shopVariants->pluck('id');

        $hasActiveOrders = \App\Models\OrderItem::whereIn('shop_product_variant_id', $shopVariantIds)
            ->whereHas('order', fn($q) => $q->whereIn('status', $activeStatuses))
            ->exists();

        if ($hasActiveOrders) {
            throw new \App\Exceptions\CustomExceptionWithMessage('custom.products.cannot_delete_has_active_orders', 422);
        }

        $variant->delete();

        return true;
    }
}
