<?php

/**
 * Stable display_type IDs — must match DisplayTypeSeeder and every environment.
 *
 * Page-specific banner variants (welcome / intro) override the default banner ID
 * when the page slug matches.
 */
return [
    'ids' => [
        'banner' => 1,
        'product' => 2,
        'shop' => 3,
        'basket' => 4,
        'schedule-basket' => 5,
        'brand' => 6,
        'recipe' => 7,
        'category' => 8,
    ],

    'page_slug_overrides' => [
        'welcome' => ['banner' => 9],
        'intro' => ['banner' => 10],
    ],

    'seed' => [
        1 => ['manual_model' => 'banner', 'image' => 'images/display/banner.png', 'allowed_page_slugs' => null],
        2 => ['manual_model' => 'product', 'image' => 'images/display/product.png', 'allowed_page_slugs' => null],
        3 => ['manual_model' => 'shop', 'image' => 'images/display/shop.png', 'allowed_page_slugs' => null],
        4 => ['manual_model' => 'basket', 'image' => 'images/display/basket.png', 'allowed_page_slugs' => null],
        5 => ['manual_model' => 'schedule-basket', 'image' => 'images/display/schedule-basket.png', 'allowed_page_slugs' => null],
        6 => ['manual_model' => 'brand', 'image' => 'images/display/brand.png', 'allowed_page_slugs' => null],
        7 => ['manual_model' => 'recipe', 'image' => 'images/display/recipe.png', 'allowed_page_slugs' => null],
        8 => ['manual_model' => 'category', 'image' => 'images/display/category.png', 'allowed_page_slugs' => null],
        9 => ['manual_model' => 'banner', 'image' => 'images/display/banner.png', 'allowed_page_slugs' => ['welcome']],
        10 => ['manual_model' => 'banner', 'image' => 'images/display/banner.png', 'allowed_page_slugs' => ['intro']],
    ],
];
