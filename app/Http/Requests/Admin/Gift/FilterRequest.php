<?php

namespace App\Http\Requests\Admin\Gift;

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
            'is_active' => ['nullable', 'boolean'],
            'available' => ['nullable', 'boolean'],
            'points_min' => ['nullable', 'integer', 'min:0'],
            'points_max' => ['nullable', 'integer', 'min:0', 'gte:points_min'],
        ];
    }
}
