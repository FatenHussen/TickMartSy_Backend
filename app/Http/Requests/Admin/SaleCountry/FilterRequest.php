<?php

namespace App\Http\Requests\Admin\SaleCountry;

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
            'is_active' => 'nullable|boolean',
        ];
    }
}
