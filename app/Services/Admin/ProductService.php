<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Services\BaseService;
use App\Http\Resources\Admin\Product\OneResource;
use App\Http\Resources\Admin\Product\AllResource;

class ProductService extends BaseService
{
    protected $model      = Product::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
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

    protected $searchableFields = [
        'category_id',
        'name',
        'description',
        'full_description',
        'sku',
        'country',
        'model',
        'price',
        'price_after_discount',
        'quantity',
        'barcode',
        'time_prepare',
        'bought_with',
        'is_instant_delivery'
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

    
    protected function handleRelations($object, array &$data)
    {
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
}
