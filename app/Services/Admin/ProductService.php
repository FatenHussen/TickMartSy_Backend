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
        'variants.shops',
        'categoryDetails.categoryDetail',
        'extraDetails',
        'variants.shopVariants.shop',
    ];

    /**
     * hasMany relations
     */
    protected $syncRelations = [
        'variants'         => 'variants',
        'categoryDetails'  => 'category_details',
        'extraDetails'     => 'extra_details',
        'shopVariants'     => 'shop_variants', // optional
    ];

    /**
     * Media handling
     */
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
        'price',
        'quantity',
        'time_prepare',
    ];
}
