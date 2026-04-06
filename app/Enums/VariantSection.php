<?php

namespace App\Enums;

enum VariantSection: string
{
    case Vertical = 'vertical';
    case Horizontal = 'horizontal';
    case Square = 'square';

    public static function values(): array
    {
        return array_map(
            fn (self $variant) => $variant->value,
            self::cases()
        );
    }
}
