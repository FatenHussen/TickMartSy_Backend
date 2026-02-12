<?php

namespace App\Http\Requests\Admin\Basket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        \Log::info('UpdateRequest::prepareForValidation - RAW', [
            'all_data' => $this->all(),
            'request_data' => $this->request->all(),
            'content_type' => $this->header('Content-Type'),
            'method' => $this->method(),
        ]);

        // Convert date format from d-m-Y to Y-m-d if provided
        if ($this->has('offer_ends_at') && $this->offer_ends_at) {
            $date = \DateTime::createFromFormat('d-m-Y', $this->offer_ends_at);
            if ($date) {
                $this->merge([
                    'offer_ends_at' => $date->format('Y-m-d'),
                ]);
            }
        }

        \Log::info('UpdateRequest::prepareForValidation - AFTER', [
            'all_data' => $this->all(),
            'has_items' => $this->has('items'),
            'items' => $this->input('items'),
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|required|integer|exists:categories,id',
            'name' => 'sometimes|required|array',
            'name.*' => 'required|string|max:255',
            'offer_ends_at' => 'nullable|date|after:today',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'sometimes|required|in:fixed,percentage',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delivery_price' => 'nullable|numeric|min:0',

            // Basket items - simplified
            'items' => 'sometimes|required|array|min:1',
            'items.*.shop_product_variant_id' => 'required|integer|exists:shop_product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
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
            'items.*.shop_product_variant_id.required' => 'معرف المنتج في المتجر مطلوب',
            'items.*.shop_product_variant_id.exists' => 'المنتج المحدد غير موجود',
            'items.*.quantity.required' => 'الكمية مطلوبة',
        ];
    }
}
