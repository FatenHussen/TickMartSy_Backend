<?php

namespace App\Http\Requests\Admin\ProductVariant;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
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
