<?php

namespace App\Http\Requests\Admin\Warranty;

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
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'description.ar' => 'nullable|string|max:2000',
            'description.en' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ];
    }
}
