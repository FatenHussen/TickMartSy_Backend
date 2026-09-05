<?php

namespace App\Support;

class ScheduleDiscount
{
    public static function isPercentage(?string $type): bool
    {
        return in_array($type, ['percentage', 'percent'], true);
    }

    public static function normalizeType(?string $type): string
    {
        return self::isPercentage($type) ? 'percentage' : 'fixed';
    }

    public static function amount(float $total, ?string $type, $value): float
    {
        $value = (float) ($value ?? 0);

        if ($value <= 0 || $total <= 0) {
            return 0;
        }

        if (self::isPercentage($type)) {
            return round($total * $value / 100, 2);
        }

        return round(min($value, $total), 2);
    }
}
