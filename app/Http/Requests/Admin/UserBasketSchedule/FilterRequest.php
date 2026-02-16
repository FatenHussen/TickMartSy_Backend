<?php

namespace App\Http\Requests\Admin\UserBasketSchedule;

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
            'user_id' => 'nullable|integer|exists:users,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'schedule_id' => 'nullable|integer|exists:schedules,id',
            'is_active' => 'nullable|boolean',
            'start_date_from' => 'nullable|date',
            'start_date_to' => 'nullable|date|after_or_equal:start_date_from',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'المستخدم المحدد غير موجود',
            'category_id.exists' => 'الفئة المحددة غير موجودة',
            'schedule_id.exists' => 'الجدولة المحددة غير موجودة',
            'start_date_to.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
        ];
    }
}
