<?php

namespace App\Support;

class ProductDiscountRules
{
    public static function value(?string $type, bool $sometimes = false): array
    {
        $rules = [];

        if ($sometimes) {
            $rules[] = 'sometimes';
        }

        $rules[] = 'nullable';
        $rules[] = 'numeric';
        $rules[] = 'min:0';

        if ($type === 'percentage') {
            $rules[] = 'max:100';
        }

        return $rules;
    }

    /**
     * @param  array<int|string, mixed>|null  $variants
     * @return array<string, array<int, string>>
     */
    public static function forVariants(?array $variants, string $prefix = 'variants'): array
    {
        $rules = [
            "{$prefix}.*.discount" => self::value(null),
        ];

        if (! is_array($variants)) {
            return $rules;
        }

        foreach ($variants as $index => $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $rules["{$prefix}.{$index}.discount"] = self::value($variant['discount_type'] ?? null);
        }

        return $rules;
    }
}
