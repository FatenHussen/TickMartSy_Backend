<?php

namespace App\Support;

use App\Models\AttributeValue;
use App\Models\Color;

class AttributeColorHex
{
    /**
     * Resolve a display hex for a color-type attribute value.
     * Prefers colors.hex via color_id; falls back to matching colors by name.
     */
    public static function forValue(?AttributeValue $value): ?string
    {
        if (!$value) {
            return null;
        }

        $hex = $value->color?->hex;
        if (is_string($hex) && $hex !== '') {
            return self::normalize($hex);
        }

        $name = $value->color?->name ?? $value->name;
        if (!is_string($name) || $name === '') {
            return null;
        }

        $matched = self::findColorByName($name);

        return $matched?->hex ? self::normalize($matched->hex) : self::normalize($name);
    }

    public static function normalize(mixed $hex): ?string
    {
        if (!is_string($hex)) {
            return null;
        }

        $trimmed = trim($hex);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^#?[0-9A-Fa-f]{6}$/', $trimmed)) {
            return str_starts_with($trimmed, '#') ? strtoupper($trimmed) : '#' . strtoupper($trimmed);
        }

        if (preg_match('/^#?[0-9A-Fa-f]{3}$/', $trimmed)) {
            $raw = ltrim($trimmed, '#');
            $expanded = $raw[0] . $raw[0] . $raw[1] . $raw[1] . $raw[2] . $raw[2];

            return '#' . strtoupper($expanded);
        }

        return null;
    }

    private static function findColorByName(string $name): ?Color
    {
        static $cache = [];

        $key = mb_strtolower(trim($name));
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $color = Color::query()
            ->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) = ?", [mb_strtolower($name)])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) = ?", [mb_strtolower($name)])
                    ->orWhereRaw('LOWER(hex) = ?', [mb_strtolower($name)])
                    ->orWhereRaw("LOWER(hex) = ?", ['#' . ltrim(mb_strtolower($name), '#')]);
            })
            ->first();

        return $cache[$key] = $color;
    }
}
