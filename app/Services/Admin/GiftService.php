<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Gift\AllResource;
use App\Http\Resources\Admin\Gift\OneResource;
use App\Models\Gift;
use App\Services\BaseService;

class GiftService extends BaseService
{
    protected $model = Gift::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = ['shopProductVariant.productVariant.product'];

    protected $singleImages = ['image'];

    protected $searchableFields = [
        'id',
        'name',
        'description',
    ];

    protected $sortableFields = [
        'id',
        'name',
        'points_required',
        'stock_quantity',
        'created_at',
    ];
    protected $pagination = true;

    /**
     * Override create to auto-fill data from ShopProductVariant
     */
    public function create($data)
    {
        $imageFromProduct = null;

        if (!empty($data['shop_product_variant_id'])) {
            // Store image path before filling
            $shopProductVariant = \App\Models\ShopProductVariant::with('productVariant.product.media')
                ->findOrFail($data['shop_product_variant_id']);
            $product = $shopProductVariant->productVariant->product;

            if (empty($data['image'])) {
                // Get first image from product media
                $firstMedia = $product->media()->first();
                if ($firstMedia && !empty($firstMedia->path)) {
                    $imageFromProduct = $firstMedia->path;
                    unset($data['image']);
                }
            }

            $data = $this->fillFromShopProductVariant($data);
        }

        $result = parent::create($data);

        // Update image after creation if we got it from product
        if ($imageFromProduct && $result->resource) {
            $gift = $this->model::find($result->resource->id);
            if ($gift) {
                $gift->update(['image' => $imageFromProduct]);
                $result = new $this->resource($gift->fresh());
            }
        }

        return $result;
    }

    /**
     * Override update to auto-fill data from ShopProductVariant
     */
    public function update($id, array $data)
    {
        $imageFromProduct = null;

        if (!empty($data['shop_product_variant_id'])) {
            // Store image path before filling
            $shopProductVariant = \App\Models\ShopProductVariant::with('productVariant.product.media')
                ->findOrFail($data['shop_product_variant_id']);
            $product = $shopProductVariant->productVariant->product;

            if (empty($data['image'])) {
                // Get first image from product media
                $firstMedia = $product->media()->first();
                if ($firstMedia && !empty($firstMedia->path)) {
                    $imageFromProduct = $firstMedia->path;
                    unset($data['image']);
                }
            }

            $data = $this->fillFromShopProductVariant($data);
        }

        $result = parent::update($id, $data);

        // Update image after update if we got it from product
        if ($imageFromProduct && $result->resource) {
            $gift = $this->model::find($result->resource->id);
            if ($gift) {
                $gift->update(['image' => $imageFromProduct]);
                $result = new $this->resource($gift->fresh());
            }
        }

        return $result;
    }

    /**
     * Fill gift data from ShopProductVariant
     */
    protected function fillFromShopProductVariant(array $data): array
    {
        $shopProductVariant = \App\Models\ShopProductVariant::with('productVariant.product', 'shop')
            ->findOrFail($data['shop_product_variant_id']);

        $productVariant = $shopProductVariant->productVariant;
        $product = $productVariant->product;

        // When shop_product_variant_id is provided, always auto-fill name from product
        // (ignore any name sent by the user)
        $shouldFillName = true;

        if ($shouldFillName) {
            // Build name like ShopProductVariant label
            $locale = app()->getLocale();

            // Get product name
            $productNameAr = $product->getTranslation('name', 'ar', false) ?? '';
            $productNameEn = $product->getTranslation('name', 'en', false) ?? '';

            // Get attributes as string
            $attributes = $productVariant->getAttributesWithDetails();
            $attributesPartsAr = [];
            $attributesPartsEn = [];

            foreach ($attributes as $attr) {
                $attrNameData = $attr['category_attribute']['name'] ?? [];
                $attrNameAr = is_array($attrNameData)
                    ? ($attrNameData['ar'] ?? $attrNameData['en'] ?? '')
                    : (string) $attrNameData;
                $attrNameEn = is_array($attrNameData)
                    ? ($attrNameData['en'] ?? $attrNameData['ar'] ?? '')
                    : (string) $attrNameData;

                $attrValueData = $attr['display_name'] ?? $attr['name'] ?? [];
                $attrValueAr = is_array($attrValueData)
                    ? ($attrValueData['ar'] ?? $attrValueData['en'] ?? '')
                    : (string) $attrValueData;
                $attrValueEn = is_array($attrValueData)
                    ? ($attrValueData['en'] ?? $attrValueData['ar'] ?? '')
                    : (string) $attrValueData;

                if ($attrValueAr === '' && !empty($attr['hex'])) {
                    $attrValueAr = (string) $attr['hex'];
                }

                if ($attrValueEn === '' && !empty($attr['hex'])) {
                    $attrValueEn = (string) $attr['hex'];
                }

                if (!empty($attrNameAr) && !empty($attrValueAr)) {
                    $attributesPartsAr[] = "{$attrNameAr}: {$attrValueAr}";
                }
                if (!empty($attrNameEn) && !empty($attrValueEn)) {
                    $attributesPartsEn[] = "{$attrNameEn}: {$attrValueEn}";
                }
            }

            $attributesStringAr = implode(' | ', $attributesPartsAr);
            $attributesStringEn = implode(' | ', $attributesPartsEn);

            // Get shop name
            $shopNameData = $shopProductVariant->shop->name;
            $shopNameAr = is_array($shopNameData)
                ? ($shopNameData['ar'] ?? $shopNameData['en'] ?? '')
                : (string) $shopNameData;
            $shopNameEn = is_array($shopNameData)
                ? ($shopNameData['en'] ?? $shopNameData['ar'] ?? '')
                : (string) $shopNameData;

            // Build label
            $labelAr = $productNameAr;
            if (!empty($attributesStringAr)) {
                $labelAr .= " ({$attributesStringAr})";
            }
            $labelAr .= " - {$shopNameAr}";

            $labelEn = $productNameEn;
            if (!empty($attributesStringEn)) {
                $labelEn .= " ({$attributesStringEn})";
            }
            $labelEn .= " - {$shopNameEn}";

            $data['name'] = [
                'ar' => $labelAr,
                'en' => $labelEn,
            ];
        }

        // Always auto-fill description from product when variant is provided
        $shouldFillDescription = true;

        if ($shouldFillDescription) {
            $data['description'] = $product->getTranslations('description') ?? ['ar' => '', 'en' => ''];
        }

        // Auto-fill category_id from product
        if (empty($data['category_id']) && !empty($product->category_id)) {
            $data['category_id'] = $product->category_id;
        }

        // Auto-fill stock from shop product variant
        if (!isset($data['stock_quantity'])) {
            $data['stock_quantity'] = $shopProductVariant->stock_quantity;
        }

        return $data;
    }

    public function bulkCreate(array $data): array
    {
        $variantIds = $data['shop_product_variant_ids'];
        unset($data['shop_product_variant_ids']);

        $created = [];

        foreach ($variantIds as $variantId) {
            $itemData = array_merge($data, ['shop_product_variant_id' => $variantId]);
            $created[] = $this->create($itemData);
        }

        return $created;
    }

    public function queryBuilder($query, $filters = [], $config = [])    {
        // Filter by is_active
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
            unset($filters['is_active']);
        }

        // Filter by points range
        if (!empty($filters['points_min'])) {
            $query->where('points_required', '>=', $filters['points_min']);
            unset($filters['points_min']);
        }

        if (!empty($filters['points_max'])) {
            $query->where('points_required', '<=', $filters['points_max']);
            unset($filters['points_max']);
        }

        // Filter by availability
        if (isset($filters['available']) && $filters['available']) {
            $query->available();
            unset($filters['available']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }
}
