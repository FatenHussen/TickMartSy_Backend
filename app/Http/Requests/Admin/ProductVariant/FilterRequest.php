<?php

namespace App\Http\Requests\Admin\ProductVariant;

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
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'quantity_min' => ['nullable', 'integer', 'min:0'],
            'quantity_max' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
