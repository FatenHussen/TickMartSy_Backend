<?php

namespace App\Http\Requests\Admin\LegalDocument;

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
            'key' => 'nullable|string|max:255',
        ];
    }
}
