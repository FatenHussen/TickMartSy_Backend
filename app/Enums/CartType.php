<?php

namespace App\Enums;

enum CartType: string
{
    case DEFAULT = 'default';
    case RECIPE = 'recipe';
    case SCHEDULE_ADMIN_CART = 'schedule_admin_cart';
    case ADMIN_CART = 'admin_cart';
}
