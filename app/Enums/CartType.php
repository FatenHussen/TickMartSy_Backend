<?php

namespace App\Enums;

enum CartType: string
{
    case DEFAULT = 'default';
    case RECIPE = 'recipe';
    case SCHEDULED_ADMIN_CART = '';
    case ADMIN_CART = 'admin_cart';
}
