<?php

namespace App\Traits;

use App\Models\Currency;

trait HasCurrencyConversion
{
    /**
     * تحويل السعر من دولار للعملة المطلوبة
     */
    public function convertPrice($priceInUSD, $currencyId = null)
    {
        // Handle null price
        if ($priceInUSD === null) {
            return [
                'amount' => null,
                'currency' => 'USD',
                'symbol' => '$',
                'formatted' => null
            ];
        }

        // Convert to float if needed
        $priceInUSD = (float) $priceInUSD;

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
    public function convertFormattedPrice($priceInUSD, $currencyId = null)
    {
        $converted = $this->convertPrice($priceInUSD, $currencyId);
        return $converted['formatted'];
    }

    /**
     * تحويل مصفوفة أسعار
     */
    public function convertPrices(array $prices, $currencyId = null)
    {
        $converted = [];
        foreach ($prices as $key => $price) {
            $converted[$key] = $this->convertPrice($price, $currencyId);
        }
        return $converted;
    }

    /**
     * إضافة معلومات العملة للـ Resource
     */
    protected function withCurrency($priceInUSD, $key = 'price')
    {
        $converted = $this->convertPrice($priceInUSD);

        return [
            $key => $converted['amount'],
            'currency' => $converted['currency'],
            'currency_symbol' => $converted['symbol'],
            $key . '_formatted' => $converted['formatted'],
        ];
    }
}
