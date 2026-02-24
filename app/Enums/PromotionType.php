<?php

namespace App\Enums;

enum PromotionType: string
{
    case OFFER = 'offer';
    case BANNER = 'banner';

    public function label(): string
    {
        return match($this) {
            self::OFFER => 'عرض',
            self::BANNER => 'بنر إعلاني',
        };
    }
}

