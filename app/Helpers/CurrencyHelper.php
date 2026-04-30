<?php

namespace App\Helpers;

use App\Models\Currency;

class CurrencyHelper
{
    public static function formatAmount(?float $amount, int $decimals = 2): ?string
    {
        if ($amount === null) {
            return null;
        }

        $formatted = number_format($amount, $decimals, '.', ',');

        return rtrim(rtrim($formatted, '0'), '.');
    }
    /**
     * تحويل السعر من عملة اليوزر إلى الدولار (العملة الأساسية)
     */
    public static function convertToBase(float $price, ?int $currencyId = null): float
    {
        if (!$currencyId) {
            $user = auth('user')->user();
            $currencyId = $user?->currency_id;
        }

        if (!$currencyId) {
            return $price;
        }

        $currency = Currency::find($currencyId);

        if (!$currency || $currency->is_default) {
            return $price;
        }

        return (float) $currency->convertToBase($price);
    }

    /**
     * للتوافق مع الكود القديم
     */
    public static function convertToUSD(float $price, ?int $currencyId = null): float
    {
        return self::convertToBase($price, $currencyId);
    }

    /**
     * تحويل السعر من الدولار (الأساسية) إلى عملة اليوزر
     */
    public static function convertFromBase(float $priceInBase, ?int $currencyId = null): array
    {
        if (!$currencyId) {
            $user = auth('user')->user();
            $currencyId = $user?->currency_id;
        }

        if (!$currencyId) {
            $currency = Currency::default()->first();
        } else {
            $currency = Currency::find($currencyId);
        }

        // fallback للعملة الافتراضية
        if (!$currency) {
            $currency = Currency::default()->first();
        }

        if (!$currency || $currency->is_default) {
            return [
                'amount'    => round($priceInBase, 2),
                'currency'  => $currency?->code ?? 'USD',
                'symbol'    => $currency?->symbol ?? '$',
                'formatted' => ($currency?->symbol ?? '$') . ' ' . self::formatAmount($priceInBase),
            ];
        }

        $convertedAmount = $currency->convertFromBase($priceInBase);

        return [
            'amount'    => $convertedAmount,
            'currency'  => $currency->code,
            'symbol'    => $currency->symbol,
            'formatted' => $currency->symbol . ' ' . self::formatAmount($convertedAmount),
        ];
    }

    /**
     * للتوافق مع الكود القديم
     */
    public static function convertFromUSD(float $priceInBase, ?int $currencyId = null): array
    {
        return self::convertFromBase($priceInBase, $currencyId);
    }

    /**
     * الحصول على عملة اليوزر
     */
    public static function getUserCurrency(): ?Currency
    {
        $user = auth('user')->user();

        if (!$user || !$user->currency_id) {
            return Currency::default()->first();
        }

        return Currency::find($user->currency_id);
    }

    /**
     * تحويل نطاق أسعار من عملة اليوزر للدولار
     */
    public static function convertPriceRangeToUSD(?float $minPrice, ?float $maxPrice, ?int $currencyId = null): array
    {
        return [
            'min' => $minPrice ? self::convertToBase($minPrice, $currencyId) : null,
            'max' => $maxPrice ? self::convertToBase($maxPrice, $currencyId) : null,
        ];
    }

    /**
     * للتوافق مع الكود القديم
     */
    public static function convertPriceRangeToBase(?float $minPrice, ?float $maxPrice, ?int $currencyId = null): array
    {
        return self::convertPriceRangeToUSD($minPrice, $maxPrice, $currencyId);
    }
}
