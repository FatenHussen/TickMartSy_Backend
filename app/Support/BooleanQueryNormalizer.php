<?php

namespace App\Support;

class BooleanQueryNormalizer
{
    /**
     * Normalize string boolean query values (e.g. "true") for Laravel validation.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        foreach ($input as $key => $value) {
            if (! is_string($key) || ! self::isBooleanKey($key)) {
                continue;
            }

            $normalized = self::normalizeValue($value);

            if ($normalized !== null) {
                $input[$key] = $normalized;
            }
        }

        return $input;
    }

    public static function isBooleanKey(string $key): bool
    {
        return str_starts_with($key, 'is_') || $key === 'active';
    }

    public static function normalizeValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 1 ? 1 : 0;
        }

        if (! is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));

        if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
            return 1;
        }

        if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
            return 0;
        }

        return null;
    }
}
