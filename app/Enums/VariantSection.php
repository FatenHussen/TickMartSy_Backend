<?php

namespace App\Enums;

/**
 * Card shape inside a section (not the section layout).
 *
 * Section presentation (slider / list / grid) uses {@see SectionLayout}.
 */
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
