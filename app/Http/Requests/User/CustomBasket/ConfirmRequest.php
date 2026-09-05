<?php

namespace App\Http\Requests\User\CustomBasket;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('user')->check();
    }

    public function rules(): array
    {
        return [
            'confirm_schedule' => ['required', 'boolean'],
            'start_date' => ['required_if:confirm_schedule,true', 'nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_schedule.required' => 'حدّد إذا بدك تكرار السلة حسب الفئة',
            'start_date.required_if' => 'تاريخ أول توصيل مطلوب عند اختيار التكرار',
        ];
    }
}
