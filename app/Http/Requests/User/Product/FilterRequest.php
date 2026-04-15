<?php

namespace App\Http\Requests\User\Product;

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
            'category_id'   => ['nullable', 'exists:categories,id'],
            'shop_id'       => ['nullable', 'exists:shops,id'],
            'price_min'     => ['nullable', 'numeric', 'min:0'],
            'price_max'     => ['nullable', 'numeric', 'min:0'],
            'country'       => ['nullable', 'string', 'max:100'],
            'name'          => ['nullable', 'string', 'max:100'],

            'type'          => ['nullable', 'in:new,trend,top_rated,offers,latest_flash_sale,recommended,for_you,search_based,most_popular'],
            'search'        => ['nullable', 'string', 'max:255'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'is_free_delivery' => ['nullable', 'boolean'],
            'is_instant_delivery' => ['nullable', 'boolean'],
            'on_sale' => ['nullable', 'boolean'],
            'in_stock_only' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'in:price_desc,price_asc,newest,oldest,rating_desc,rating_asc,rating'],

            // Attribute filters
            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*' => ['integer', 'exists:attribute_values,id']
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Support comma-separated attribute_values
        if ($this->has('attribute_values') && is_string($this->attribute_values)) {
            $this->merge([
                'attribute_values' => array_map('intval', explode(',', $this->attribute_values))
            ]);
        }
    }
}
