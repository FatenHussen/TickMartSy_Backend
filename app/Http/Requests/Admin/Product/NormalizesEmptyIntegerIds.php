<?php

namespace App\Http\Requests\Admin\Product;

trait NormalizesEmptyIntegerIds
{
    protected function normalizeEmptyIntegerIds(): void
    {
        $fields = [
            'category_id',
            'brand_id',
            'country_id',
            'sale_country_id',
            'unit_id',
            'warranty_id',
            'vendor_id',
        ];

        $merge = [];

        foreach ($fields as $field) {
            if (!$this->exists($field)) {
                continue;
            }

            $value = $this->input($field);
            if ($this->isEmptyIntegerId($value)) {
                $merge[$field] = null;
            }
        }

        if ($this->exists('warranty_period')) {
            $period = $this->input('warranty_period');
            if ($period === '' || (is_string($period) && trim($period) === '')) {
                $merge['warranty_period'] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    private function isEmptyIntegerId(mixed $value): bool
    {
        if ($value === '' || $value === null) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        return $value === 0 || $value === 0.0 || $value === '0';
    }
}
