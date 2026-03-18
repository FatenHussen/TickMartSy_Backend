<?php

namespace App\Enums;

enum RateableType: string
{
    case PRODUCT = 'product';
    case BRAND = 'brand';
    case SHOP = 'shop';
    case DELIVERY = 'delivery';
    case RECIPE = 'recipe';
    case BASKET = 'basket';
    case SCHEDULED_BASKET = 'schedule_basket';
    case ORDER = 'order';
}
