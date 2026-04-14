<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'end_date' => ['required', 'date', 'after:now'],
            'is_active' => 'nullable|boolean',
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'integer|exists:products,id',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'vendor_id' => 'sometimes|nullable|exists:vendors,id',
        ];
    }
}
