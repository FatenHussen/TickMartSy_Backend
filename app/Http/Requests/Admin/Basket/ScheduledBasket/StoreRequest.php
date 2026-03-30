<?php

namespace App\Http\Requests\Admin\Basket\ScheduledBasket;

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
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:fixed,percentage',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'delivery_price' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',

            // Schedules (multiple schedules)
            'schedules' => 'required|array|min:1',
            'schedules.*.title' => 'nullable|array',
            'schedules.*.title.*' => 'nullable|string|max:255',
            'schedules.*.number_of_days' => 'required|integer|min:1',
            'schedules.*.discount_type' => 'nullable|in:fixed,percentage',
            'schedules.*.discount_value' => 'nullable|numeric|min:0',
            'schedules.*.is_active' => 'nullable|boolean',
            'schedules.*.is_default' => 'nullable|boolean',

            // Basket items - for scheduled baskets
            'items' => 'required|array|min:1',
            'items.*.shop_product_variant_id' => 'required|integer|exists:shop_product_variants,id', // Primary variant
            'items.*.shop_product_variant_ids' => 'nullable|array', // Alternative variants (optional)
            'items.*.shop_product_variant_ids.*' => 'required|integer|exists:shop_product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.is_required' => 'required|boolean',
            'items.*.is_extra' => 'required|boolean',
            'items.*.min_quantity' => 'nullable|integer|min:1',
            'items.*.max_quantity' => 'nullable|integer|min:1',


            'badges'          => 'nullable|array',
            'badges.*.id'  => 'required|integer|exists:badges,id',
            'badges.*.position'  => 'required|in:top,bottom',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'الفئة مطلوبة',
            'category_id.exists' => 'الفئة المحددة غير موجودة',
            'name.required' => 'اسم السلة مطلوب',
            'name.*.required' => 'اسم السلة مطلوب لجميع اللغات',

            'schedules.required' => 'الجدولات مطلوبة',
            'schedules.min' => 'يجب إضافة جدولة واحدة على الأقل',
            'schedules.*.number_of_days.required' => 'عدد الأيام للتوصيل مطلوب',
            'schedules.*.number_of_days.min' => 'عدد الأيام يجب أن يكون على الأقل 1',

            'items.required' => 'يجب إضافة منتج واحد على الأقل للسلة',
            'items.min' => 'يجب إضافة منتج واحد على الأقل للسلة',
            'items.*.shop_product_variant_id.required' => 'المنتج الأساسي مطلوب',
            'items.*.shop_product_variant_id.exists' => 'المنتج الأساسي المحدد غير موجود',
            'items.*.shop_product_variant_ids.*.exists' => 'أحد المنتجات البديلة غير موجود',
            'items.*.quantity.required' => 'الكمية مطلوبة',
            'items.*.is_required.required' => 'حقل "مطلوب" مطلوب',
            'items.*.is_extra.required' => 'حقل "إضافي" مطلوب',
        ];
    }
}
