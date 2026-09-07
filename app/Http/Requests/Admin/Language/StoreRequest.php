<?php

namespace App\Http\Requests\Admin\Language;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:10|unique:languages,code',
            'native_name' => 'required|string|max:100',
            'direction' => 'required|in:ltr,rtl',
            'is_active' => 'required|boolean',
            'is_default' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'flag_icon' => 'nullable|image|mimes:jpeg,jpg,png,webp,svg|max:2048',
        ];
    }
}
