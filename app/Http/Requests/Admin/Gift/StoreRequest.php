<?php

namespace App\Http\Requests\Admin\Gift;

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
            'shop_product_variant_id' => ['nullable', 'integer', 'exists:shop_product_variants,id'],

            'name' => ['required_without:shop_product_variant_id', 'array'],
            'name.ar' => ['required_without:shop_product_variant_id', 'string', 'max:255'],
            'name.en' => ['required_without:shop_product_variant_id', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],

            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'points_required' => ['required', 'integer', 'min:1'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],

            'terms_conditions' => ['nullable', 'array'],
            'terms_conditions.ar' => ['nullable', 'string'],
            'terms_conditions.en' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'shop_product_variant_id.exists' => 'المنتج المحدد غير موجود',
            'name.required_without' => 'الاسم مطلوب إذا لم يتم تحديد منتج',
            'name.ar.required_without' => 'الاسم بالعربي مطلوب إذا لم يتم تحديد منتج',
            'name.en.required_without' => 'الاسم بالإنجليزي مطلوب إذا لم يتم تحديد منتج',
            'points_required.required' => 'النقاط المطلوبة مطلوبة',
            'points_required.min' => 'النقاط المطلوبة يجب أن تكون على الأقل 1',
        ];
    }
}
