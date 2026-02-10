<?php

namespace App\Enums;


enum ComplaintType: string
{
    case PRODUCT = 'product';
    case ORDER = 'order';
    case DRIVER = 'driver';
    case MERCHANT = 'merchant';
}
