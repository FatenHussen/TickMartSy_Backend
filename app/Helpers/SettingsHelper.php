<?php

namespace App\Helpers;

use App\Models\SystemSetting;

class SettingsHelper
{
    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return SystemSetting::get($key, $default);
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value, string $type = 'string'): bool
    {
        return SystemSetting::set($key, $value, $type);
    }

    /**
     * Get points system settings
     */
    public static function getPointsSettings(): array
    {
        return [
            'enabled' => self::get('points_enabled', true),
            'currency_rate' => self::get('point_to_currency_rate', 0.01),
            'currency_symbol' => self::get('currency_symbol', '$'),
            'currency_code' => self::get('currency_code', 'USD'),
            // إعدادات الاستبدال
            'min_exchange_points' => self::get('min_exchange_points', 100),
            'max_exchange_points' => self::get('max_exchange_points', 10000),
            'points_expiry_months' => self::get('points_expiry_months', 12),
            'exchange_enabled' => self::get('points_exchange_enabled', true),
        ];
    }

    /**
     * Get exchange settings
     */
    public static function getExchangeSettings(): array
    {
        return [
            'min_points' => self::get('min_exchange_points', 100),
            'max_points' => self::get('max_exchange_points', 10000),
            'coupon_enabled' => self::get('exchange_coupon_enabled', true),
            'free_delivery_enabled' => self::get('exchange_free_delivery_enabled', true),
            'gifts_enabled' => self::get('exchange_gifts_enabled', true),
            'free_delivery_points' => self::get('free_delivery_points_cost', 200),
            'coupon_discount_rate' => self::get('coupon_discount_rate', 0.01), // 1 point = 0.01$ discount
        ];
    }

    /**
     * Get delivery settings
     */
    public static function getDeliverySettings(): array
    {
        return [
            'default_fee' => self::get('default_delivery_fee', 5.00),
            'free_threshold' => self::get('free_delivery_threshold', 50.00),
        ];
    }

    /**
     * Get app settings
     */
    public static function getAppSettings(): array
    {
        return [
            'name' => self::get('app_name', 'Tikmool'),
            'version' => self::get('app_version', '1.0.0'),
            'maintenance_mode' => self::get('maintenance_mode', false),
        ];
    }

    /**
     * Check if points system is enabled
     */
    public static function isPointsEnabled(): bool
    {
        return (bool) self::get('points_enabled', true);
    }

    /**
     * Check if app is in maintenance mode
     */
    public static function isMaintenanceMode(): bool
    {
        return (bool) self::get('maintenance_mode', false);
    }

    /**
     * Format points to currency
     */
    public static function formatPointsToCurrency(int $points): array
    {
        $settings = self::getPointsSettings();
        $currencyValue = $points * $settings['currency_rate'];
        
        return [
            'value' => $currencyValue,
            'formatted' => $settings['currency_symbol'] . number_format($currencyValue, 2),
            'symbol' => $settings['currency_symbol'],
            'code' => $settings['currency_code'],
        ];
    }
}