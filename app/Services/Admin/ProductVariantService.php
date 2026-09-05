<?php

namespace App\Services\Admin;

use App\Exceptions\DeleteConfirmationRequiredException;
use App\Http\Resources\Admin\ProductVariant\AllResource;
use App\Http\Resources\Admin\ProductVariant\OneResource;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Services\Base\MediaService;
use App\Services\Base\VariantDeleteImpactService;
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
    protected $sortableFields = ['id', 'price', 'quantity', 'created_at'];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query->with($this->relations);

        // Remove fields that are not direct columns
        unset($filters['attributes_values_ids']);

        $categoryIds = array_values(array_unique(array_filter(array_merge(
            is_array($filters['category_ids'] ?? null) ? $filters['category_ids'] : [],
            isset($filters['category_id']) ? [(int) $filters['category_id']] : [],
        ))));

        if (!empty($categoryIds)) {
            $subtreeIds = Category::expandIdsToSubtrees($categoryIds);
            $query->whereHas('product', function (Builder $q) use ($subtreeIds) {
                $q->whereIn('category_id', $subtreeIds);
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

        // Filter by price range
        if (array_key_exists('price_min', $filters) && $filters['price_min'] !== null) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (array_key_exists('price_max', $filters) && $filters['price_max'] !== null) {
            $query->where('price', '<=', $filters['price_max']);
        }

        if (array_key_exists('quantity_min', $filters) && $filters['quantity_min'] !== null) {
            $query->where('quantity', '>=', $filters['quantity_min']);
        }

        if (array_key_exists('quantity_max', $filters) && $filters['quantity_max'] !== null) {
            $query->where('quantity', '<=', $filters['quantity_max']);
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

        // Handle translatable name
        if (isset($data['name']) && is_array($data['name'])) {
            foreach ($data['name'] as $locale => $value) {
                $variant->setTranslation('name', $locale, $value ?? '');
            }
            unset($data['name']);
        }

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

    /**
     * معاينة أثر الحذف دون تنفيذه.
     */
    public function deleteImpact($id): array
    {
        $variant = ProductVariant::findOrFail($id);

        return (new VariantDeleteImpactService())->forProductVariant($variant);
    }

    /**
     * الحذف مسموح دائماً، لكنه يتطلب تأكيداً صريحاً إذا كان يؤثر على بيانات مرتبطة.
     */
    public function deleteWithConfirmation($id, bool $confirmed = false): array
    {
        $variant = ProductVariant::findOrFail($id);

        $impact = (new VariantDeleteImpactService())->forProductVariant($variant);

        if ($impact['requires_confirmation'] && !$confirmed) {
            throw new DeleteConfirmationRequiredException($impact);
        }

        $variant->delete();

        return $impact;
    }

    public function delete($id): bool
    {
        $this->deleteWithConfirmation($id, (bool) request()->boolean('confirm'));

        return true;
    }
}
