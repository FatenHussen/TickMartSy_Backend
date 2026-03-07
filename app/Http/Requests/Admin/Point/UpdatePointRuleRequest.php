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
            'title' => 'sometimes|array',
            'title.ar' => 'required_with:title|string|max:255',
            'title.en' => 'required_with:title|string|max:255',
            'value' => 'required|integer|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.array' => 'العنوان يجب أن يكون مصفوفة تحتوي على اللغات',
            'title.ar.required_with' => 'العنوان بالعربي مطلوب',
            'title.ar.string' => 'العنوان بالعربي يجب أن يكون نص',
            'title.ar.max' => 'العنوان بالعربي يجب ألا يتجاوز 255 حرف',
            'title.en.required_with' => 'العنوان بالإنجليزي مطلوب',
            'title.en.string' => 'العنوان بالإنجليزي يجب أن يكون نص',
            'title.en.max' => 'العنوان بالإنجليزي يجب ألا يتجاوز 255 حرف',
            'value.required' => 'قيمة النقاط مطلوبة',
            'value.integer' => 'قيمة النقاط يجب أن تكون رقم صحيح',
            'value.min' => 'قيمة النقاط يجب أن تكون 0 أو أكثر',
            'min_order_amount.numeric' => 'الحد الأدنى لقيمة الطلب يجب أن يكون رقم',
            'min_order_amount.min' => 'الحد الأدنى لقيمة الطلب يجب أن يكون 0 أو أكثر',
            'is_active.boolean' => 'حالة القاعدة يجب أن تكون صحيح أو خطأ',
        ];
    }
}
