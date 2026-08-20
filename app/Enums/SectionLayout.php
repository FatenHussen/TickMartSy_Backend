<?php

namespace App\Enums;

enum SectionLayout: string
{
    case Slider = 'slider';
    case List = 'list';
    case Grid = 'grid';

    public static function values(): array
    {
        return array_map(
            fn (self $layout) => $layout->value,
            self::cases()
        );
    }
}
