<?php

namespace App\Services\Admin;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Services\BaseService;
use App\Http\Resources\Admin\Product\OneResource;
use App\Http\Resources\Admin\Product\AllResource;
use App\Exceptions\CustomExceptionWithMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService extends BaseService
{
    protected $model      = Product::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
        'unitOption',
        'brand',
        'originCountry',
        'saleCountry',
        'variants',
        'variants.shopVariants',
        'categoryDetails.categoryDetail',
        'extraDetails',
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

        $resource = parent::create($data);
        return $resource;
    }

    public function update($id, array $data)
    {
        if (array_key_exists('unit_id', $data)) {
            $unit = \App\Models\Unit::query()->find($data['unit_id']);
            $data['unit'] = $unit?->getTranslation('name', app()->getLocale(), false)
                ?? $unit?->getTranslation('name', 'en', false)
                ?? $unit?->getTranslation('name', 'ar', false);
        }

        $resource = parent::update($id, $data);
        return $resource;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
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
            $query->whereIn('category_id', $categoryIds);
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

            $relationObj = $object->$relation();

            $relationObj->delete();

            foreach ($items as $item) {
                $relationObj->create($item);
            }
        }

        // if (isset($data['variants']) && is_array($data['variants'])) {
        //     $variantsData = $data['variants'];
        //     unset($data['variants']);

        //     // Build map of existing variants keyed by sorted attributes_values_ids
        //     $existingVariants = $object->variants()->get();
        //     $existingVariantsMap = [];
        //     foreach ($existingVariants as $existingVariant) {
        //         $sortedIds = $existingVariant->attributes_values_ids ?? [];
        //         sort($sortedIds);
        //         $key = json_encode($sortedIds);
        //         $existingVariantsMap[$key] = $existingVariant;
        //     }

        //     $mediaService = new \App\Services\Base\MediaService();
        //     $variantIndexMap = [];
        //     $matchedVariantIds = [];

        //     // Process incoming variants: update matched, create new
        //     foreach ($variantsData as $index => $variantItem) {
        //         $existingImagesIds = $variantItem['existing_images_ids'] ?? [];
        //         $variantImages = $variantItem['images'] ?? [];
        //         unset($variantItem['existing_images_ids'], $variantItem['images']);

        //         // Create matching key for incoming variant
        //         $incomingIds = $variantItem['attributes_values_ids'] ?? [];
        //         sort($incomingIds);
        //         $matchKey = json_encode($incomingIds);

        //         // Check if variant exists
        //         if (isset($existingVariantsMap[$matchKey])) {
        //             // Update existing variant
        //             $variant = $existingVariantsMap[$matchKey];
        //             $variant->update($variantItem);
        //             $matchedVariantIds[] = $variant->id;
        //         } else {
        //             // Create new variant
        //             $variant = $object->variants()->create($variantItem);
        //         }

        //         $variantIndexMap[$index] = $variant;

        //         // Handle variant images with existing_images_ids
        //         if (!empty($existingImagesIds)) {
        //             // This is an update - delete images not in existing list
        //             $currentMedia = $variant->media()->where('collection', 'variant_images')->get();
        //             foreach ($currentMedia as $media) {
        //                 if (!in_array($media->id, $existingImagesIds)) {
        //                     $media->delete();
        //                 }
        //             }
        //         }

        //         // Upload new images
        //         if (!empty($variantImages)) {
        //             foreach ($variantImages as $file) {
        //                 if ($file instanceof \Illuminate\Http\UploadedFile) {
        //                     $mediaService->upload($variant, $file, 'variant_images');
        //                 }
        //             }
        //         }
        //     }

        //     // Soft delete unmatched existing variants
        //     foreach ($existingVariants as $existingVariant) {
        //         if (!in_array($existingVariant->id, $matchedVariantIds)) {
        //             $existingVariant->delete();
        //         }
        //     }

        //     if (isset($data['shop_variants']) && is_array($data['shop_variants'])) {
        //         $shopVariantsData = $data['shop_variants'];
        //         unset($data['shop_variants']);

        //         foreach ($shopVariantsData as $svItem) {
        //             $variantIndex = $svItem['variant_index'] ?? null;

        //             if ($variantIndex === null || !isset($variantIndexMap[$variantIndex])) {
        //                 continue;
        //             }

        //             $variant = $variantIndexMap[$variantIndex];

        //             $variant->shopVariants()->create([
        //                 'shop_id'  => $svItem['shop_id'],
        //                 'price'    => $svItem['price'] ?? null,
        //                 'quantity' => $svItem['quantity'] ?? null,
        //             ]);
        //         }
        //     }
        // }
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

            // إرسال إشعار للفيندور
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

            // إرسال إشعار للفيندور
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
