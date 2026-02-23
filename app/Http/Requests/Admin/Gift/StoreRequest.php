<?php

namespace App\Http\Requests\Admin\Gift;

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
            'name' => ['required', 'array'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],

            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'points_required' => ['required', 'integer', 'min:1'],
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
            'name.required' => 'الاسم مطلوب',
            'name.ar.required' => 'الاسم بالعربي مطلوب',
            'name.en.required' => 'الاسم بالإنجليزي مطلوب',
            'points_required.required' => 'النقاط المطلوبة مطلوبة',
            'points_required.min' => 'النقاط المطلوبة يجب أن تكون على الأقل 1',
        ];
    }
}
