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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delivery_price' => 'nullable|numeric|min:0',

            // Schedule (default delivery schedule)
            'schedule' => 'required|array',
            'schedule.title' => 'nullable|array',
            'schedule.title.*' => 'nullable|string|max:255',
            'schedule.number_of_days' => 'required|integer|min:1',
            'schedule.discount_type' => 'nullable|in:fixed,percentage',
            'schedule.discount_value' => 'nullable|numeric|min:0',
            'schedule.is_active' => 'nullable|boolean',

            // Basket items - for scheduled baskets
            'items' => 'required|array|min:1',
            'items.*.shop_product_variant_ids' => 'required|array|min:1',
            'items.*.shop_product_variant_ids.*' => 'required|integer|exists:shop_product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.is_required' => 'required|boolean',
            'items.*.is_extra' => 'required|boolean',
            'items.*.min_quantity' => 'nullable|integer|min:1',
            'items.*.max_quantity' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'الفئة مطلوبة',
            'category_id.exists' => 'الفئة المحددة غير موجودة',
            'name.required' => 'اسم السلة مطلوب',
            'name.*.required' => 'اسم السلة مطلوب لجميع اللغات',

            'schedule.required' => 'الجدولة الافتراضية مطلوبة',
            'schedule.number_of_days.required' => 'عدد الأيام للتوصيل مطلوب',
            'schedule.number_of_days.min' => 'عدد الأيام يجب أن يكون على الأقل 1',

            'items.required' => 'يجب إضافة منتج واحد على الأقل للسلة',
            'items.min' => 'يجب إضافة منتج واحد على الأقل للسلة',
            'items.*.shop_product_variant_ids.required' => 'معرفات المنتجات في المتجر مطلوبة',
            'items.*.shop_product_variant_ids.*.exists' => 'أحد المنتجات المحددة غير موجود',
            'items.*.quantity.required' => 'الكمية مطلوبة',
            'items.*.is_required.required' => 'حقل "مطلوب" مطلوب',
            'items.*.is_extra.required' => 'حقل "إضافي" مطلوب',
        ];
    }
}
