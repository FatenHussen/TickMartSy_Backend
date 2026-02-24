<?php

namespace App\Http\Requests\Admin\Gift;

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
            'name' => ['sometimes', 'array'],
            'name.ar' => ['sometimes', 'string', 'max:255'],
            'name.en' => ['sometimes', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],

            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'points_required' => ['sometimes', 'integer', 'min:1'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],

            'terms_conditions' => ['nullable', 'array'],
            'terms_conditions.ar' => ['nullable', 'string'],
            'terms_conditions.en' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.ar.string' => 'الاسم بالعربي يجب أن يكون نص',
            'name.en.string' => 'الاسم بالإنجليزي يجب أن يكون نص',
            'points_required.min' => 'النقاط المطلوبة يجب أن تكون على الأقل 1',
        ];
    }
}
