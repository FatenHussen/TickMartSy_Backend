<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Services\BaseService;
use App\Http\Resources\Admin\Product\OneResource;
use App\Http\Resources\Admin\Product\AllResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService extends BaseService
{
    protected $model      = Product::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
        'brand',
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
        'seo_image' => [
            'collection' => 'seo',
            'type'       => 'single',
        ],
        'thumbnail' => [
            'collection' => 'thumbnail',
            'type'       => 'single',
        ],
    ];

    protected $searchableFields = [
        'category_id',
        'name',
        'description',
        'full_description',
        'sku',
        'country',
        'model',
        'price',
        'cost_price',
        'price_after_discount',
        'quantity',
        'unit',
        'barcode',
        'time_prepare',
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
        'price',
        'created_at',
        'category_id',
        'name',
        'sku',
        'country',
        'model',
        'quantity',
        'time_prepare',
    ];

    public function create($data)
    {
        $resource = parent::create($data);
        return $resource;
    }

    public function update($id, array $data)
    {
        $resource = parent::update($id, $data);
        return $resource;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by shop_id if provided
        if (!empty($filters['shop_id'])) {
            $query->whereHas('variants.shopVariants', function ($q) use ($filters) {
                $q->where('shop_id', $filters['shop_id']);
            });
            unset($filters['shop_id']);
        }

        // Apply other filters
        foreach ($filters as $key => $value) {
            if ($value === null) continue;
            $query->where($key, $value);
        }

        // Search functionality
        if (!empty($config['search'])) {
            $search = $config['search'];
            $query->where(function ($q) use ($search) {
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

    protected function handleRelations($object, array &$data)
    {
        // Handle icon_ids
        if (isset($data['icon_ids']) && is_array($data['icon_ids'])) {
            $object->icons()->sync($data['icon_ids']);
            unset($data['icon_ids']);
        }

        foreach ($this->syncRelations as $relation => $requestKey) {
            if (in_array($requestKey, ['variants', 'shop_variants'])) {
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

        if (isset($data['variants']) && is_array($data['variants'])) {
            $variantsData = $data['variants'];
            unset($data['variants']);

            $object->variants()->delete();

            $mediaService = new \App\Services\Base\MediaService();

            $variantIndexMap = [];

            foreach ($variantsData as $index => $variantItem) {
                $variantImages = $variantItem['images'] ?? [];
                unset($variantItem['images']);

                $variant = $object->variants()->create($variantItem);

                $variantIndexMap[$index] = $variant;

                if (!empty($variantImages)) {
                    foreach ($variantImages as $file) {
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            $mediaService->upload($variant, $file, 'variant_images');
                        }
                    }
                }
            }

            if (isset($data['shop_variants']) && is_array($data['shop_variants'])) {
                $shopVariantsData = $data['shop_variants'];
                unset($data['shop_variants']);

                foreach ($shopVariantsData as $svItem) {
                    $variantIndex = $svItem['variant_index'] ?? null;

                    if ($variantIndex === null || !isset($variantIndexMap[$variantIndex])) {
                        continue;
                    }

                    $variant = $variantIndexMap[$variantIndex];

                    $variant->shopVariants()->create([
                        'shop_id'  => $svItem['shop_id'],
                        'price'    => $svItem['price'] ?? null,
                        'quantity' => $svItem['quantity'] ?? null,
                    ]);
                }
            }
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
                throw new \Exception('يمكن قبول المنتجات المعلقة فقط');
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
                throw new \Exception('يمكن رفض المنتجات المعلقة فقط');
            }

            if (empty($reason)) {
                throw new \Exception('يجب إدخال سبب الرفض');
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
