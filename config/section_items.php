<?php
return [
    'banner' => [
        'item_type' => 'App\Models\Banner',
        'url'   => 'admin/banners',
    ],
    'product' => [
        'item_type' => 'App\Models\Product',
        'url'   => 'admin/products',
    ],
    'shop' => [
        'item_type' => 'App\Models\Shop',
        'url'   => 'admin/shops',
    ],
    'restaurant' => [
        'item_type' => 'App\Models\Shop',
        'url'   => 'admin/shops?is_restaurant=1',
    ],
    'brand' => [
        'item_type' => 'App\Models\Brand',
        'url'   => 'admin/brands',
    ],
    'recipe' => [
        'item_type' => 'App\Models\Recipe',
        'url'   => 'admin/recipes',
    ],
    'basket' => [
        'item_type' => 'App\Models\Basket',
        'url'   => 'admin/baskets',
    ],
    'category' => [
        'item_type' => 'App\Models\Category',
        'url'   => 'admin/categories',
    ],
    // 'vendor' => [
    //     'item_type' => 'App\Models\Vendor',
    //     'url'   => 'admin/shop',
    // ],
    // 'suggested-basket' => [],
    // 'basket' => [],
];
