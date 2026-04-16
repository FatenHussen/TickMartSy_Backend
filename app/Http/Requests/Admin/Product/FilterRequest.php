<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shop_id'        => 'nullable|integer',
            'category_id'    => 'nullable|integer|exists:categories,id',
            'brand_id'       => 'nullable|integer|exists:brands,id',
            'vendor_id'      => 'nullable|integer|exists:vendors,id',
            'category_attribute_id' => 'nullable|integer|exists:category_attributes,id',
            'category_attribute_ids' => 'nullable|array',
            'category_attribute_ids.*' => 'integer|exists:category_attributes,id',
            'stock_sort'     => 'nullable|in:asc,desc',
        ];
    }
}
