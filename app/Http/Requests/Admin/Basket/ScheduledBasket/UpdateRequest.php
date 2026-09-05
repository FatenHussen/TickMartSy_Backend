<?php

namespace App\Http\Requests\Admin\Basket\ScheduledBasket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
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

        if ($this->file('images') instanceof UploadedFile) {
            $this->merge([
                'images' => [$this->file('images')],
            ]);
        }
    }



    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|required|integer|exists:categories,id',
            'category_ids' => 'sometimes|required|array|min:1',
            'category_ids.*' => 'required|integer|exists:categories,id',
            'name' => 'sometimes|required|array',
            'name.*' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:2000',
            'schedule_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('schedules', 'id')->where('is_active', true),
            ],
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'required_with:discount|in:fixed,percentage',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif',
            'deleted_image_ids' => 'nullable|array',
            'deleted_image_ids.*' => 'integer',
            'delivery_price' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',

            // Basket items - for scheduled baskets
            'items' => 'sometimes|required|array|min:1',
            'items.*.shop_product_variant_id' => 'required|integer|exists:shop_product_variants,id', // Primary variant
            'items.*.shop_product_variant_ids' => 'nullable|array', // Alternative variants (optional)
            'items.*.shop_product_variant_ids.*' => 'required|integer|exists:shop_product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.is_required' => 'required|boolean',
            'items.*.is_extra' => 'required|boolean',
            'items.*.min_quantity' => 'nullable|integer|min:1',
            'items.*.max_quantity' => 'nullable|integer|min:1',


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
            'schedule_id.required' => 'الجدولة مطلوبة',
            'schedule_id.exists' => 'الجدولة المحددة غير موجودة',

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
