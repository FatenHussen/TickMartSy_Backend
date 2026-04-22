<?php

namespace App\Http\Requests\Admin\Basket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (!$this->has('category_ids') && $this->filled('category_id')) {
            $this->merge([
                'category_ids' => [(int) $this->input('category_id')],
            ]);
        }

        if (!$this->filled('category_id') && is_array($this->input('category_ids')) && !empty($this->input('category_ids'))) {
            $this->merge([
                'category_id' => (int) $this->input('category_ids')[0],
            ]);
        }

        // Convert date format from d-m-Y to Y-m-d if provided
        if ($this->has('offer_ends_at') && $this->offer_ends_at) {
            $date = \DateTime::createFromFormat('d-m-Y', $this->offer_ends_at);
            if ($date) {
                $this->merge([
                    'offer_ends_at' => $date->format('Y-m-d'),
                ]);
            }
        }

        if ($this->file('images') instanceof UploadedFile) {
            $this->merge([
                'images' => [$this->file('images')],
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'required|integer|exists:categories,id',
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:2000',
            'offer_ends_at' => 'nullable|date|after:today',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:fixed,percentage',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif',
            'deleted_image_ids' => 'nullable|array',
            'deleted_image_ids.*' => 'integer',
            'delivery_price' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',

            // Basket items - simplified
            'items' => 'required|array|min:1',
            'items.*.shop_product_variant_id' => 'required|integer|exists:shop_product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',


            'badges'          => 'nullable|array',
            'badges.*' => 'integer|exists:badges,id',
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
