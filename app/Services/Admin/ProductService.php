<?php

namespace App\Services\Admin;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use App\Services\BaseService;
use App\Http\Resources\Admin\Product\OneResource;
use App\Http\Resources\Admin\Product\AllResource;
use App\Exceptions\CustomExceptionWithMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService extends BaseService
{
    /** Preferred Tikmool platform vendor id when that row exists. */
    public const PLATFORM_VENDOR_ID = 1;

    /**
     * Real vendors.id for site-owned products.
     * Prefers id=1, then a vendor that owns the default shop, then the first vendor.
     */
    public static function resolvePlatformVendorId(): ?int
    {
        if (Vendor::query()->whereKey(self::PLATFORM_VENDOR_ID)->exists()) {
            return self::PLATFORM_VENDOR_ID;
        }

        $fromDefaultShop = Shop::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->orderBy('id')
            ->value('vendor_id');

        if ($fromDefaultShop) {
            return (int) $fromDefaultShop;
        }

        $id = Vendor::query()->orderBy('id')->value('id');

        return $id ? (int) $id : null;
    }

    protected $model      = Product::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
        'unitOption',
        'warranty',
        'brand',
        'originCountry',
        'saleCountry',
        'variants',
        'variants.shopVariants',
        'categoryDetails.categoryDetail',
        'extraDetails.category',
        'variants.shopVariants.shop',
    ];

    protected $syncRelations = [
        'variants'         => 'variants',
        'categoryDetails'  => 'category_details',
        'extraDetails'     => 'extra_details',
        'variants.shopVariants'     => 'shop_variants',
        'badges'   => 'badges',
    ];

    protected $mediaCollections = [
        'images' => [
            'collection' => 'product',
            'type'       => 'multiple',
        ],
        'variant_images' => [
            'collection' => 'variant',
            'type'       => 'multiple',
        ],
    ];

    protected $singleImages = ['thumbnail', 'seo_image'];

    protected $searchableFields = [
        'product_number',
        'category_id',
        'name',
        'description',
        'full_description',
        'sku',
        'country_id',
        'sale_country_id',
        'model',
        'price',
        'cost_price',
        'quantity',
        'unit',
        'barcode',
        'time_prepare',
        'expiry_date',
        'bought_with',
        'is_instant_delivery',
        'is_visible',
        'discount_type',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $sortableFields = [
        'id',
        'product_number',
        'price',
        'created_at',
        'category_id',
        'name',
        'sku',
        'country_id',
        'sale_country_id',
        'model',
        'quantity',
        'stock',
        'vendor_id',
        'time_prepare',
        'expiry_date',
    ];

    public function create($data)
    {
        if (array_key_exists('unit_id', $data)) {
            $unit = \App\Models\Unit::query()->find($data['unit_id']);
            $data['unit'] = $unit?->getTranslation('name', app()->getLocale(), false)
                ?? $unit?->getTranslation('name', 'en', false)
                ?? $unit?->getTranslation('name', 'ar', false);
        }

        if (empty($data['sale_country_id'])) {
            $syriaId = \App\Models\SaleCountry::query()
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", ['Syria'])
                ->value('id');
            if ($syriaId) {
                $data['sale_country_id'] = $syriaId;
            }
        }

        $this->applySaleChannel($data);

        $object = $this->model::create($data);
        $this->handleSingleImages($object, $data);
        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);
        $this->ensureDefaultVariant($object);
        $this->ensureDefaultShopLinks($object);

        $object->refresh();

        return new $this->resource($object);
    }

    public function update($id, array $data)
    {
        if (array_key_exists('unit_id', $data)) {
            $unit = \App\Models\Unit::query()->find($data['unit_id']);
            $data['unit'] = $unit?->getTranslation('name', app()->getLocale(), false)
                ?? $unit?->getTranslation('name', 'en', false)
                ?? $unit?->getTranslation('name', 'ar', false);
        }

        DB::beginTransaction();

        $object = $this->applyAdminCityRestriction($this->model::query())->findOrFail($id);
        if (property_exists($object, 'translatable')) {
            foreach ($object->translatable as $field) {
                if (isset($data[$field])) {
                    $incomingTranslations = is_array($data[$field]) ? $data[$field] : [];
                    $existingTranslations = method_exists($object, 'getTranslations')
                        ? $object->getTranslations($field)
                        : [];

                    $object->setTranslations($field, array_merge($existingTranslations, $incomingTranslations));
                    unset($data[$field]);
                }
            }
        }

        $this->handleSingleImages($object, $data);

        if (array_key_exists('sale_channel', $data) || array_key_exists('shop_variants', $data)) {
            if (!array_key_exists('sale_channel', $data)) {
                $data['sale_channel'] = $object->sale_channel ?? 'platform';
            }
            $this->applySaleChannel($data);
        }

        $object->update($data);
        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);
        $this->ensureDefaultVariant($object);
        $this->ensureDefaultShopLinks($object);

        $object->refresh();

        DB::commit();

        return new $this->resource($object);
    }

    /**
     * platform → site product (vendor Tikmool + default platform shop, no shop picker).
     * shop → must send shop_variants; vendor taken from the first selected shop.
     */
    private function applySaleChannel(array &$data): void
    {
        $channel = $data['sale_channel'] ?? 'platform';
        if (!in_array($channel, ['platform', 'shop'], true)) {
            $channel = 'platform';
        }
        $data['sale_channel'] = $channel;

        if ($channel === 'platform') {
            $platformVendorId = self::resolvePlatformVendorId();
            if ($platformVendorId) {
                $data['vendor_id'] = $platformVendorId;
            }
            // Clear any client-sent shops; ensureDefaultShopLinks attaches platform default.
            $data['shop_variants'] = [];

            return;
        }

        if (!empty($data['shop_variants']) && is_array($data['shop_variants'])) {
            $firstShopId = collect($data['shop_variants'])
                ->pluck('shop_id')
                ->filter()
                ->first();

            if ($firstShopId) {
                $vendorId = Shop::query()->whereKey($firstShopId)->value('vendor_id');
                if ($vendorId) {
                    $data['vendor_id'] = $vendorId;
                }
            }
        }
    }

    private function ensureDefaultVariant(Product $product): void
    {
        if ($product->variants()->exists()) {
            return;
        }

        $product->variants()->create([
            'sku' => $product->sku,
            'model' => $product->model,
            'barcode' => $product->barcode,
            'price' => $product->price ?? 0,
            'quantity' => $product->quantity ?? 0,
            'attributes_values_ids' => [],
            'is_active' => true,
        ]);
    }

    /**
     * Platform products without a shop link get the Tikmool default branch.
     * Shop-channel products only use explicitly sent shop_variants.
     */
    private function ensureDefaultShopLinks(Product $product): void
    {
        $channel = $product->sale_channel ?? 'platform';
        if ($channel === 'shop') {
            return;
        }

        $product->unsetRelation('variants');
        $product->load('variants.shopVariants');

        $unlinkedVariants = $product->variants->filter(
            fn ($variant) => $variant->shopVariants->isEmpty()
        );

        if ($unlinkedVariants->isEmpty()) {
            return;
        }

        $defaultShop = $this->resolvePlatformDefaultShop();
        if (!$defaultShop) {
            Log::warning('Platform product saved without shop links and no platform default shop found', [
                'product_id' => $product->id,
                'vendor_id' => self::resolvePlatformVendorId(),
            ]);

            return;
        }

        foreach ($unlinkedVariants as $variant) {
            $variant->shopVariants()->create([
                'shop_id' => $defaultShop->id,
                'cost_price' => null,
            ]);
        }
    }

    private function resolvePlatformDefaultShop(): ?Shop
    {
        $vendorId = self::resolvePlatformVendorId();
        if (!$vendorId) {
            return null;
        }

        $defaultShop = Shop::query()
            ->where('vendor_id', $vendorId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if ($defaultShop) {
            return $defaultShop;
        }

        return Shop::query()
            ->where('vendor_id', $vendorId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by product ID
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
            unset($filters['id']);
        }

        if (!empty($filters['product_number'])) {
            $query->where('product_number', $filters['product_number']);
            unset($filters['product_number']);
        }

        $categoryIds = array_values(array_unique(array_filter(array_merge(
            $this->normalizeIdInput($filters['category_id'] ?? null),
            $this->normalizeIdInput($filters['category_ids'] ?? null)
        ))));
        unset($filters['category_id'], $filters['category_ids']);

        if (!empty($categoryIds)) {
            $query->whereIn('category_id', Category::expandIdsToSubtrees($categoryIds));
        }

        // Filter by shop_id if provided
        if (!empty($filters['shop_id'])) {
            $query->whereHas('variants.shopVariants', function ($q) use ($filters) {
                $q->where('shop_id', $filters['shop_id']);
            });
            unset($filters['shop_id']);
        }

        // Filter by vendor_id
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
            unset($filters['vendor_id']);
        }

        // Filter by one or more category attributes through variant attribute values
        $categoryAttributeIds = array_values(array_unique(array_filter(array_merge(
            $this->normalizeIdInput($filters['category_attribute_id'] ?? null),
            $this->normalizeIdInput($filters['category_attribute_ids'] ?? null)
        ))));
        unset($filters['category_attribute_id'], $filters['category_attribute_ids']);

        if (!empty($categoryAttributeIds)) {
            $attributeValueIds = AttributeValue::query()
                ->whereIn('category_attribute_id', $categoryAttributeIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            if (!empty($attributeValueIds)) {
                $query->whereHas('variants', function ($variantsQuery) use ($attributeValueIds) {
                    $variantsQuery->where(function ($jsonQuery) use ($attributeValueIds) {
                        foreach ($attributeValueIds as $attributeValueId) {
                            $jsonQuery->orWhereJsonContains('attributes_values_ids', $attributeValueId);
                        }
                    });
                });
            } else {
                // If selected attribute has no values, return no products
                $query->whereRaw('1 = 0');
            }
        }

        // Sort by stock
        if (!empty($filters['stock_sort'])) {
            $order = $filters['stock_sort'] === 'desc' ? 'desc' : 'asc';
            $query->orderBy('stock', $order);
            unset($filters['stock_sort']);
        }

        // Apply other filters
        foreach ($filters as $key => $value) {
            if ($value === null) continue;
            $query->where($key, $value);
        }

        // Search functionality
        if (!empty($config['search'])) {
            $search = trim((string) $config['search']);
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }

                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'LIKE', "%$search%");
                }
            });
        }

        // Sorting
        if (!empty($config['sortField']) && in_array($config['sortField'], $this->sortableFields ?? [])) {
            $order = strtolower($config['sortOrder'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($config['sortField'], $order);
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

    protected function handleRelations($object, array &$data)
    {
        // Handle icon_ids
        if (isset($data['icon_ids']) && is_array($data['icon_ids'])) {
            $object->icons()->sync($data['icon_ids']);
            unset($data['icon_ids']);
        }

        // Handle badges before other relations
        if (isset($data['badges']) && is_array($data['badges'])) {
            $badgesData = $data['badges'];
            unset($data['badges']);

            $syncData = [];
            foreach ($badgesData as $badge) {
                if (is_array($badge) && isset($badge['id'])) {
                    $badgeId = $badge['id'];
                    unset($badge['id']);
                    $syncData[$badgeId] = $badge;
                } elseif (is_numeric($badge)) {
                    $syncData[$badge] = [];
                }
            }

            if (!empty($syncData)) {
                $object->badges()->sync($syncData);
            }
        }

        // Handle product media with existing_media_ids
        if (isset($data['existing_media_ids']) || isset($data['media'])) {
            $existingIds = $data['existing_media_ids'] ?? [];
            $newFiles = $data['media'] ?? [];

            $mediaService = new \App\Services\Base\MediaService();

            // Delete images not in existing_media_ids
            $currentMedia = $object->media()->where('collection', 'product')->get();
            foreach ($currentMedia as $media) {
                if (!in_array($media->id, $existingIds)) {
                    $media->delete();
                }
            }

            // Upload new images
            if (is_array($newFiles)) {
                $mediaService->uploadMultiple($object, $newFiles, 'product');
            }

            unset($data['existing_media_ids'], $data['media']);
        }

        foreach ($this->syncRelations as $relation => $requestKey) {
            if (in_array($requestKey, ['variants', 'shop_variants', 'badges'])) {
                continue;
            }

            if (!array_key_exists($requestKey, $data)) {
                continue;
            }

            $items = $data[$requestKey];
            unset($data[$requestKey]);

            if (!method_exists($object, $relation)) {
                continue;
            }

            // Handle many-to-many relationships with pivot data (like extra_details)
            if ($requestKey === 'extra_details') {
                $syncData = [];
                foreach ($items as $item) {
                    if (isset($item['product_extra_detail_id'])) {
                        $syncData[$item['product_extra_detail_id']] = [
                            'quantity' => $item['quantity'] ?? 0,
                            'price' => $item['price'] ?? 0,
                        ];
                    }
                }
                $object->extraDetails()->sync($syncData);
            } else {
                // Handle hasMany relationships (create/delete)
                $relationObj = $object->$relation();
                $relationObj->delete();

                foreach ($items as $item) {
                    $relationObj->create($item);
                }
            }
        }

        $this->syncVariantsAndShopLinks($object, $data);
    }

    private function syncVariantsAndShopLinks(Product $product, array &$data): void
    {
        $hasVariantsPayload = array_key_exists('variants', $data) && is_array($data['variants']);
        $hasShopVariantsPayload = array_key_exists('shop_variants', $data) && is_array($data['shop_variants']);

        if (!$hasVariantsPayload && !$hasShopVariantsPayload) {
            return;
        }

        $variantsData = $hasVariantsPayload ? $data['variants'] : [];
        $shopVariantsData = $hasShopVariantsPayload ? $data['shop_variants'] : [];
        unset($data['variants'], $data['shop_variants']);

        $mediaService = new \App\Services\Base\MediaService();
        $variantIndexMap = [];
        $keptIds = [];

        if ($hasVariantsPayload) {
            foreach ($variantsData as $index => $variantItem) {
                if (!is_array($variantItem)) {
                    continue;
                }

                $existingImagesIds = $variantItem['existing_images_ids'] ?? [];
                $variantImages = $variantItem['images'] ?? [];
                $variantId = $variantItem['id'] ?? null;
                unset(
                    $variantItem['existing_images_ids'],
                    $variantItem['images'],
                    $variantItem['shops'],
                    $variantItem['attributes'],
                    $variantItem['id']
                );

                $payload = array_intersect_key($variantItem, array_flip([
                    'name',
                    'sku',
                    'model',
                    'barcode',
                    'price',
                    'discount',
                    'discount_type',
                    'quantity',
                    'attributes_values_ids',
                    'is_trend',
                    'is_active',
                ]));

                if (!array_key_exists('price', $payload) || $payload['price'] === null) {
                    $payload['price'] = $product->price ?? 0;
                }

                $variant = $variantId
                    ? $product->variants()->whereKey($variantId)->first()
                    : null;

                if ($variant) {
                    $variant->update($payload);
                } else {
                    $variant = $product->variants()->create($payload);
                }

                $variantIndexMap[(int) $index] = $variant;
                $keptIds[] = $variant->id;

                if (!empty($existingImagesIds) || $variantId) {
                    $currentMedia = $variant->media()->get();
                    foreach ($currentMedia as $media) {
                        if (!in_array($media->id, $existingImagesIds)) {
                            $media->delete();
                        }
                    }
                }

                if (!empty($variantImages) && is_array($variantImages)) {
                    foreach ($variantImages as $file) {
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            $mediaService->upload($variant, $file, 'variant');
                        }
                    }
                }
            }

            if ($keptIds === []) {
                $product->variants()->get()->each->delete();
            } else {
                $product->variants()
                    ->whereNotIn('id', $keptIds)
                    ->get()
                    ->each
                    ->delete();
            }
        } else {
            foreach ($product->variants()->orderBy('id')->get() as $index => $variant) {
                $variantIndexMap[$index] = $variant;
            }
        }

        if (!$hasShopVariantsPayload) {
            return;
        }

        foreach ($variantIndexMap as $variant) {
            $variant->shopVariants()->delete();
        }

        foreach ($shopVariantsData as $svItem) {
            if (!is_array($svItem)) {
                continue;
            }

            $variantIndex = $svItem['variant_index'] ?? null;
            if ($variantIndex === null || !isset($variantIndexMap[(int) $variantIndex])) {
                continue;
            }

            if (empty($svItem['shop_id'])) {
                continue;
            }

            $variantIndexMap[(int) $variantIndex]->shopVariants()->create([
                'shop_id' => $svItem['shop_id'],
                'cost_price' => $svItem['cost_price'] ?? null,
            ]);
        }
    }

    /**
     * قبول المنتج
     */
    public function approve(int $id): Product
    {
        return DB::transaction(function () use ($id) {
            $product = Product::with(['vendor'])->findOrFail($id);

            if ($product->approval_status !== \App\Enums\ProductApprovalStatus::PENDING) {
                throw new CustomExceptionWithMessage('custom.products.only_pending_can_be_approved');
            }

            $product->update([
                'approval_status' => \App\Enums\ProductApprovalStatus::APPROVED,
                'rejection_reason' => null,
            ]);

            $this->sendApprovalNotification($product, 'approved');

            return $product->fresh(['vendor', 'category', 'brand']);
        });
    }

    /**
     * رفض المنتج
     */
    public function reject(int $id, string $reason): Product
    {
        return DB::transaction(function () use ($id, $reason) {
            $product = Product::with(['vendor'])->findOrFail($id);

            if ($product->approval_status !== \App\Enums\ProductApprovalStatus::PENDING) {
                throw new CustomExceptionWithMessage('custom.products.only_pending_can_be_rejected');
            }

            if (empty($reason)) {
                throw new CustomExceptionWithMessage('custom.products.rejection_reason_required');
            }

            $product->update([
                'approval_status' => \App\Enums\ProductApprovalStatus::REJECTED,
                'rejection_reason' => $reason,
            ]);

            $this->sendApprovalNotification($product, 'rejected');

            return $product->fresh(['vendor', 'category', 'brand']);
        });
    }

    /**
     * إرسال إشعار للفيندور
     */
    protected function sendApprovalNotification(Product $product, string $action): void
    {
        try {
            $notificationService = app(\App\Services\Vendor\VendorNotificationService::class);

            if ($action === 'approved') {
                $notificationService->notifyProductApproved($product);
            } elseif ($action === 'rejected') {
                $notificationService->notifyProductRejected($product, $product->rejection_reason ?? '');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send product approval notification', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
            ]);
        }
    }
}
