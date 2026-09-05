<?php

namespace App\Http\Requests\Admin\ProductVariant;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['sku', 'model', 'barcode'] as $field) {
            if (!$this->exists($field)) {
                continue;
            }

            $value = $this->input($field);
            if (is_string($value) && trim($value) === '') {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'name'                    => 'sometimes|array',
            'name.ar'                 => 'nullable|string|max:255',
            'name.en'                 => 'nullable|string|max:255',
            'sku'                     => 'sometimes|nullable|string|unique:product_variants,sku,' . $this->route('product_variant'),
            'model'                   => 'sometimes|nullable|string|max:255',
            'barcode'                 => 'sometimes|nullable|string|max:255',
            'price'                   => 'sometimes|nullable|numeric|min:0',
            'discount'                => 'sometimes|nullable|integer|min:0|max:100',
            'discount_type'           => 'sometimes|nullable|in:none,percentage,fixed',
            'quantity'                => 'sometimes|nullable|integer|min:0',
            'attributes_values_ids'   => 'sometimes|array',
            'attributes_values_ids.*' => 'integer|exists:attribute_values,id',
            'is_trend'                => 'sometimes|boolean',
            'is_active'               => 'sometimes|boolean',
            'images'                  => 'sometimes|array',
            'images.*'                => 'file|image|max:5120',
            'existing_images_ids'     => 'sometimes|array',
            'existing_images_ids.*'   => 'integer',
        ];
    }
}
