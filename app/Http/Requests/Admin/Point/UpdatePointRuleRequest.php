<?php

namespace App\Http\Requests\Admin\Point;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePointRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|integer|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'قيمة النقاط مطلوبة',
            'value.integer' => 'قيمة النقاط يجب أن تكون رقم صحيح',
            'value.min' => 'قيمة النقاط يجب أن تكون 0 أو أكثر',
            'min_order_amount.numeric' => 'الحد الأدنى لقيمة الطلب يجب أن يكون رقم',
            'min_order_amount.min' => 'الحد الأدنى لقيمة الطلب يجب أن يكون 0 أو أكثر',
            'is_active.boolean' => 'حالة القاعدة يجب أن تكون صحيح أو خطأ',
        ];
    }
}
