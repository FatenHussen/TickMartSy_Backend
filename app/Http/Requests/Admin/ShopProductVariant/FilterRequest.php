<?php

namespace App\Http\Requests\Admin\ShopProductVariant;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'cost_price_min' => ['nullable', 'numeric', 'min:0'],
            'cost_price_max' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
