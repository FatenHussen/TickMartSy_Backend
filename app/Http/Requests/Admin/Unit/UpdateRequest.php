<?php

namespace App\Http\Requests\Admin\Unit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name.ar' => 'nullable|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
