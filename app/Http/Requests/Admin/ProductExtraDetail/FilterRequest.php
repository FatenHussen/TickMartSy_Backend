<?php

namespace App\Http\Requests\Admin\ProductExtraDetail;

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
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'nullable|boolean',
        ];
    }
}
