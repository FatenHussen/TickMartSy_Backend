<?php

namespace App\Http\Requests\Admin\Promotion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => 'required|string|max:255',
            'name.ar' => 'required|string|max:255',
            'description' => ['required', 'array'],
            'description.en' => 'required|string',
            'description.ar' => 'required|string',
            'type' => 'required|in:simple_discount,spend_x_discount,buy_x_get_y',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'min_spend' => 'nullable|numeric|min:0',
            'buy_quantity' => 'nullable|integer|min:1',
            'get_quantity' => 'nullable|integer|min:1',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            // 'gift_product_ids' => 'nullable|array',
            // 'gift_product_ids.*' => 'exists:shop_product_variants,id',
        ];
    }
}
