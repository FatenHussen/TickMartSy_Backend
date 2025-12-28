<?php

namespace App\Http\Requests\Admin\Language;

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
            'code' => 'nullable|string|max:10',
            'native_name' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'direction' => 'nullable|in:ltr,rtl',
        ];
    }
}
