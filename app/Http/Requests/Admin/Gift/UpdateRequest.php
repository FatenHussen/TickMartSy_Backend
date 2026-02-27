<?php

namespace App\Http\Requests\Admin\Gift;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data for validation - remove empty name/description if shop_product_variant_id is provided
     */
    protected function prepareForValidation()
    {
        if ($this->has('shop_product_variant_id') && !empty($this->shop_product_variant_id)) {
            // Check if name is empty (all translations are empty strings)
            if ($this->has('name') && is_array($this->name)) {
                $nameValues = array_filter($this->name, fn($v) => !empty($v));
                if (empty($nameValues)) {
                    // Remove name from request so validation passes
                    $this->request->remove('name');
                }
            }

            // Check if description is empty (all translations are empty strings)
            if ($this->has('description') && is_array($this->description)) {
                $descValues = array_filter($this->description, fn($v) => !empty($v));
                if (empty($descValues)) {
                    $this->request->remove('description');
                }
            }
        }
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
            'points_required' => ['sometimes', 'integer', 'min:1'],
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
            'name.ar.string' => 'الاسم بالعربي يجب أن يكون نص',
            'name.en.string' => 'الاسم بالإنجليزي يجب أن يكون نص',
            'points_required.min' => 'النقاط المطلوبة يجب أن تكون على الأقل 1',
        ];
    }
}
