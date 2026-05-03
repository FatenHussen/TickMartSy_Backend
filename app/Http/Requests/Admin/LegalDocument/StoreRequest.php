<?php

namespace App\Http\Requests\Admin\LegalDocument;

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
            'key' => ['required', 'string', 'max:255', Rule::unique('legal_documents', 'key')],
            'title.en' => 'required|string',
            'title.ar' => 'required|string',
            'content.en' => 'required|string',
            'content.ar' => 'required|string',
        ];
    }
}
