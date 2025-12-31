<?php

namespace App\Http\Requests\Admin\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $languageId = $this->route('languages')?->id
            ?? $this->route('languages');

        return [
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('languages', 'code')->ignore($languageId),
            ],
            'native_name' => 'nullable|string|max:100',
            'direction' => 'nullable|in:ltr,rtl',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'flag_icon' => 'nullable|string|max:255',
        ];
    }
}
