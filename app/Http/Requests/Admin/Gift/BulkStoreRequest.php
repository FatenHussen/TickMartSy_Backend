<?php

namespace App\Http\Requests\Admin\Gift;

use App\Http\Requests\BaseRequest;

class BulkStoreRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'shop_product_variant_ids'   => ['required', 'array', 'min:1'],
            'shop_product_variant_ids.*' => ['required', 'integer', 'exists:shop_product_variants,id'],
            'points_required'            => ['required', 'integer', 'min:1'],
            'stock_quantity'             => ['nullable', 'integer', 'min:0'],
            'is_active'                  => ['nullable', 'boolean'],
            'category_id'               => ['nullable', 'integer', 'exists:categories,id'],
            'terms_conditions'           => ['nullable', 'array'],
            'terms_conditions.ar'        => ['nullable', 'string'],
            'terms_conditions.en'        => ['nullable', 'string'],
        ];
    }
}
