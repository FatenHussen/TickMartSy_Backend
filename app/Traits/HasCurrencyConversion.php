<?php

namespace App\Traits;

use App\Models\Currency;

trait HasCurrencyConversion
{
    /**
     * تحويل السعر من الدولار (الأساسية) للعملة المطلوبة
     */
    public function convertPrice($priceInBase, $currencyId = null)
    {
        if ($priceInBase === null) {
            $defaultCurrency = Currency::default()->first();
            return [
                'amount'    => null,
                'currency'  => $defaultCurrency?->code ?? 'USD',
                'symbol'    => $defaultCurrency?->symbol ?? '$',
                'formatted' => null,
            ];
        }

        $priceInBase = (float) $priceInBase;

        if (!$currencyId) {
            $user = auth('user')->user();
            $currencyId = $user?->currency_id;
        }

        $currency = $currencyId
            ? Currency::find($currencyId)
            : Currency::default()->first();

        if (!$currency) {
            $currency = Currency::default()->first();
        }

        if (!$currency || $currency->is_default) {
            return [
                'amount'    => round($priceInBase, 2),
                'currency'  => $currency?->code ?? 'USD',
                'symbol'    => $currency?->symbol ?? '$',
                'formatted' => ($currency?->symbol ?? '$') . ' ' . number_format($priceInBase, 2),
            ];
        }

        $convertedAmount = $currency->convertFromBase($priceInBase);

        return [
            'amount'    => $convertedAmount,
            'currency'  => $currency->code,
            'symbol'    => $currency->symbol,
            'formatted' => $currency->symbol . ' ' . number_format($convertedAmount, 2),
        ];
    }

    public function convertFormattedPrice($priceInBase, $currencyId = null)
    {
        $converted = $this->convertPrice($priceInBase, $currencyId);
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
    protected function withCurrency($priceInBase, $key = 'price')
    {
        $converted = $this->convertPrice($priceInBase);

        return [
            $key                  => $converted['amount'],
            'currency'            => $converted['currency'],
            'currency_symbol'     => $converted['symbol'],
            $key . '_formatted'   => $converted['formatted'],
        ];
    }
}
