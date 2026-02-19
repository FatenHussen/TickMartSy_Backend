<?php

namespace App\Http\Requests\Admin\Package;

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
            'name' => 'sometimes|required|array',
            'name.ar' => 'required_with:name|string|max:255',
            'name.en' => 'required_with:name|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
            'monthly_orders_limit' => 'nullable|integer|min:0',
            'free_delivery_count' => 'sometimes|required|integer|min:0',
            'discount_percentage' => 'sometimes|required|numeric|min:0|max:100',
            'points_bonus' => 'sometimes|required|integer|min:0',
            'is_active' => 'sometimes|required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الباقة مطلوب',
            'name.array' => 'اسم الباقة يجب أن يكون مصفوفة',
            'name.ar.required_with' => 'اسم الباقة بالعربي مطلوب',
            'name.en.required_with' => 'اسم الباقة بالإنجليزي مطلوب',
            'price.required' => 'السعر مطلوب',
            'price.numeric' => 'السعر يجب أن يكون رقم',
            'price.min' => 'السعر يجب أن يكون أكبر من أو يساوي 0',
            'duration_days.required' => 'مدة الباقة مطلوبة',
            'duration_days.integer' => 'مدة الباقة يجب أن تكون رقم صحيح',
            'duration_days.min' => 'مدة الباقة يجب أن تكون يوم واحد على الأقل',
            'monthly_orders_limit.integer' => 'حد الطلبات الشهرية يجب أن يكون رقم صحيح',
            'monthly_orders_limit.min' => 'حد الطلبات الشهرية يجب أن يكون 0 أو أكثر',
            'free_delivery_count.required' => 'عدد التوصيلات المجانية مطلوب',
            'free_delivery_count.integer' => 'عدد التوصيلات المجانية يجب أن يكون رقم صحيح',
            'free_delivery_count.min' => 'عدد التوصيلات المجانية يجب أن يكون 0 أو أكثر',
            'discount_percentage.required' => 'نسبة الخصم مطلوبة',
            'discount_percentage.numeric' => 'نسبة الخصم يجب أن تكون رقم',
            'discount_percentage.min' => 'نسبة الخصم يجب أن تكون 0 أو أكثر',
            'discount_percentage.max' => 'نسبة الخصم يجب أن تكون 100 أو أقل',
            'points_bonus.required' => 'نقاط المكافأة مطلوبة',
            'points_bonus.integer' => 'نقاط المكافأة يجب أن تكون رقم صحيح',
            'points_bonus.min' => 'نقاط المكافأة يجب أن تكون 0 أو أكثر',
            'is_active.required' => 'حالة الباقة مطلوبة',
            'is_active.boolean' => 'حالة الباقة يجب أن تكون صحيح أو خطأ',
        ];
    }
}
