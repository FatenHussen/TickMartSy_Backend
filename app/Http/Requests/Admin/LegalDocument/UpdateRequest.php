<?php

namespace App\Http\Requests\Admin\LegalDocument;

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
        return [
            'title.en' => 'nullable|string',
            'title.ar' => 'nullable|string',
            'content.en'  => 'nullable|string',
            'content.ar'  => 'nullable|string',
        ];
    }
}
