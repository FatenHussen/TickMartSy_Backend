<?php

namespace App\Enums;

enum PriceVarianceType: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';
}
