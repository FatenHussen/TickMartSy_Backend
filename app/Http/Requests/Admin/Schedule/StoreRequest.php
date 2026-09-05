<?php

namespace App\Http\Requests\Admin\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use NormalizesSchedulePayload;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeNormalizedSchedulePayload(requireAllLocalesForName: true);
    }

    public function rules(): array
    {
        $rules = [
            'interval_days' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp',
            'badges' => 'nullable|array',
            'badges.*.id' => 'required|integer|exists:badges,id',
            'badges.*.position' => 'nullable|in:top,bottom',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'required|string|max:255';
            $rules["description.$locale"] = 'nullable|string|max:2000';
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
