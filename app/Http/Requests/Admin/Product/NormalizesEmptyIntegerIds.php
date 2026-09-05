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
            'warranty_period',
            'vendor_id',
        ];

        $merge = [];

        foreach ($fields as $field) {
            if (!$this->exists($field)) {
                continue;
            }

            $value = $this->input($field);
            if ($value === '' || (is_string($value) && trim($value) === '')) {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
