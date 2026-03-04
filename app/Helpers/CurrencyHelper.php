<?php

namespace App\Helpers;

use App\Models\Currency;

class CurrencyHelper
{
    /**
     * تحويل السعر من عملة اليوزر إلى دولار (للفلترة)
     *
     * @param float $price السعر بعملة اليوزر
     * @param int|null $currencyId معرف العملة (إذا null يأخذ من اليوزر المسجل)
     * @return float السعر بالدولار
     */
    public static function convertToUSD(float $price, ?int $currencyId = null): float
    {
        // إذا ما في currency_id، نجيبه من اليوزر
        if (!$currencyId) {
            $user = auth('user')->user();
            $currencyId = $user?->currency_id;
        }

        // إذا ما في عملة أو العملة دولار، نرجع السعر كما هو
        if (!$currencyId) {
            return $price;
        }

        $currency = Currency::find($currencyId);

        // إذا العملة دولار أو مش موجودة، نرجع السعر كما هو
        if (!$currency || $currency->code === 'USD') {
            return $price;
        }

        // نحول من عملة اليوزر للدولار
        return $currency->convertToUSD($price);
    }

    /**
     * تحويل السعر من دولار إلى عملة اليوزر (للعرض)
     *
     * @param float $priceInUSD السعر بالدولار
     * @param int|null $currencyId معرف العملة
     * @return array معلومات السعر المحول
     */
    public static function convertFromUSD(float $priceInUSD, ?int $currencyId = null): array
    {
        if (!$currencyId) {
            $user = auth('user')->user();
            $currencyId = $user?->currency_id;
        }

        if (!$currencyId) {
            $currency = Currency::default()->first() ?? Currency::where('code', 'USD')->first();
        } else {
            $currency = Currency::find($currencyId);
        }

        if (!$currency || $currency->code === 'USD') {
            return [
                'amount' => round($priceInUSD, 2),
                'currency' => 'USD',
                'symbol' => '$',
                'formatted' => '$' . number_format($priceInUSD, 2)
            ];
        }

        $convertedAmount = $currency->convertFromUSD($priceInUSD);

        return [
            'amount' => $convertedAmount,
            'currency' => $currency->code,
            'symbol' => $currency->symbol,
            'formatted' => $currency->symbol . ' ' . number_format($convertedAmount, 2)
        ];
    }

    /**
     * الحصول على عملة اليوزر
     *
     * @return Currency|null
     */
    public static function getUserCurrency(): ?Currency
    {
        $user = auth('user')->user();

        if (!$user || !$user->currency_id) {
            return Currency::default()->first() ?? Currency::where('code', 'USD')->first();
        }

        return Currency::find($user->currency_id);
    }

    /**
     * تحويل نطاق أسعار من عملة اليوزر للدولار
     *
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @param int|null $currencyId
     * @return array ['min' => float|null, 'max' => float|null]
     */
    public static function convertPriceRangeToUSD(?float $minPrice, ?float $maxPrice, ?int $currencyId = null): array
    {
        return [
            'min' => $minPrice ? self::convertToUSD($minPrice, $currencyId) : null,
            'max' => $maxPrice ? self::convertToUSD($maxPrice, $currencyId) : null,
        ];
    }
}
