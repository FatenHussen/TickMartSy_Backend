<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_restaurant' => ['nullable', 'boolean'],
            'parent_id' => [
                'nullable',
                // 'integer',
                function ($attribute, $value, $fail) {
                    // إذا القيمة 0 أو null، نسمح فيها (للفئات الأب)
                    if ($value === 0 || $value === '0' || $value === null) {
                        return;
                    }

                    // إذا القيمة موجودة، لازم تكون ID موجود بالـ categories
                    if (!\App\Models\Category::where('id', $value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                }
            ]
        ];
    }
}
