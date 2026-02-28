<?php

namespace App\Http\Requests\Admin\Shop;

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
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'area_id' => 'nullable|integer|exists:areas,id',
            'is_active' => 'nullable|boolean',
        ];
    }
}
