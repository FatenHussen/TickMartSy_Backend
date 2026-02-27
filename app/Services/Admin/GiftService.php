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
        if (!empty($data['shop_product_variant_id'])) {
            $data = $this->fillFromShopProductVariant($data);
        }

        return parent::create($data);
    }

    /**
     * Override update to auto-fill data from ShopProductVariant
     */
    public function update($id, array $data)
    {
        if (!empty($data['shop_product_variant_id'])) {
            $data = $this->fillFromShopProductVariant($data);
        }

        return parent::update($id, $data);
    }

    /**
     * Fill gift data from ShopProductVariant
     */
    protected function fillFromShopProductVariant(array $data): array
    {
        $shopProductVariant = \App\Models\ShopProductVariant::with('productVariant.product')
            ->findOrFail($data['shop_product_variant_id']);

        $productVariant = $shopProductVariant->productVariant;
        $product = $productVariant->product;

        // Auto-fill name from product + variant
        if (empty($data['name'])) {
            $variantName = '';
            if (!empty($productVariant->attribute_values)) {
                $values = collect($productVariant->attribute_values)->pluck('value')->toArray();
                $variantName = ' - ' . implode(' / ', $values);
            }
            $data['name'] = [
                'ar' => ($product->name['ar'] ?? '') . $variantName,
                'en' => ($product->name['en'] ?? '') . $variantName,
            ];
        }

        // Auto-fill description from product
        if (empty($data['description'])) {
            $data['description'] = $product->description ?? ['ar' => '', 'en' => ''];
        }

        // Auto-fill image from product
        if (empty($data['image']) && !empty($product->main_image)) {
            $data['image'] = $product->main_image;
        }

        // Auto-fill stock from shop product variant
        if (!isset($data['stock_quantity'])) {
            $data['stock_quantity'] = $shopProductVariant->stock_quantity;
        }

        return $data;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
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
