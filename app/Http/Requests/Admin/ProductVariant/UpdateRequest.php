<?php

namespace App\Http\Requests\Admin\ProductVariant;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'                    => 'sometimes|array',
            'name.ar'                 => 'nullable|string|max:255',
            'name.en'                 => 'nullable|string|max:255',
            'sku'                     => 'sometimes|nullable|string|unique:product_variants,sku,' . $this->route('product_variant'),
            'model'                   => 'sometimes|nullable|string|max:255',
            'barcode'                 => 'sometimes|nullable|string|max:255',
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
