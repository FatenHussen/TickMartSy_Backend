<?php

namespace App\Http\Requests\Admin\Schedule;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;

class StoreRequest extends FormRequest
{
    protected array $locales = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->locales = Language::active()->pluck('code')->toArray();
        $data = $this->all();

        // Prepare translatable name field
        $prepared = [];
        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $prepared[$locale] = $data['name'][$locale];
            }
        }
        $this->merge(['name' => $prepared]);
    }

    public function rules(): array
    {
        $rules = [
            'interval_days' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
        ];

        // Add locale-specific validation for name
        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'required|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'interval_days.required' => 'عدد الأيام مطلوب',
            'interval_days.integer' => 'عدد الأيام يجب أن يكون رقم صحيح',
            'interval_days.min' => 'عدد الأيام يجب أن يكون على الأقل 1',
            'discount_type.in' => 'نوع الخصم يجب أن يكون percentage أو fixed',
            'discount_value.numeric' => 'قيمة الخصم يجب أن تكون رقم',
            'discount_value.min' => 'قيمة الخصم يجب أن تكون أكبر من أو تساوي 0',
        ];
    }
}
