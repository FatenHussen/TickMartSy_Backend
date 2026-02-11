<?php

namespace App\Http\Requests\Admin\Basket;

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
            'num_varieties' => 'nullable|integer|min:0',
            'offer_ends_at' => 'nullable|date|after:today',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:fixed,percentage',
            'rating' => 'nullable|numeric|min:0|max:5',
            'num_sold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delivery_price' => 'nullable|numeric|min:0',
            
            // Basket items
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.shop_product_variant_id' => 'nullable|integer|exists:shop_product_variants,id',
            'items.*.shop_product_variant_ids' => 'nullable|array',
            'items.*.shop_product_variant_ids.*' => 'integer|exists:shop_product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.is_required' => 'nullable|boolean',
            'items.*.is_extra' => 'nullable|boolean',
            'items.*.min_quantity' => 'nullable|integer|min:1',
            'items.*.max_quantity' => 'nullable|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'الفئة مطلوبة',
            'category_id.exists' => 'الفئة المحددة غير موجودة',
            'name.required' => 'اسم السلة مطلوب',
            'name.*.required' => 'اسم السلة مطلوب لجميع اللغات',
            'items.required' => 'يجب إضافة منتج واحد على الأقل للسلة',
            'items.min' => 'يجب إضافة منتج واحد على الأقل للسلة',
            'items.*.product_id.required' => 'معرف المنتج مطلوب',
            'items.*.product_id.exists' => 'المنتج المحدد غير موجود',
            'items.*.variant_id.required' => 'معرف الصنف مطلوب',
            'items.*.variant_id.exists' => 'الصنف المحدد غير موجود',
            'items.*.quantity.required' => 'الكمية مطلوبة',
            'items.*.price.required' => 'السعر مطلوب',
        ];
    }
}