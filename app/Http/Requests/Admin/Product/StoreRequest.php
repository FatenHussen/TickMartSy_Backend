<?php

// namespace App\Http\Requests\Admin\Product;

// use Illuminate\Foundation\Http\FormRequest;
// use App\Models\Language;

// class StoreRequest extends FormRequest
// {
//     protected array $locales = [];

//     public function authorize(): bool
//     {
//         return true;
//     }

//     protected function prepareForValidation(): void
//     {
//         $this->locales = Language::active()->pluck('code')->toArray();
//         $data = $this->all();

//         $translatableFields = [
//             'name',
//             'description',
//             'full_description',
//             'country',
//         ];

//         foreach ($translatableFields as $field) {
//             $prepared = [];
//             foreach ($this->locales as $locale) {
//                 if (isset($data[$field][$locale])) {
//                     $prepared[$locale] = $data[$field][$locale];
//                 }
//             }
//             $this->merge([$field => $prepared]);
//         }
//     }

//     public function rules(): array
//     {
//         $rules = [
//             'category_id'           => 'required|exists:categories,id',
//             'sku'                   => 'nullable|string|unique:products,sku',
//             'model'                 => 'nullable|string|unique:products,model',
//             'price'                 => 'required|integer|min:0',
//             'price_after_discount'  => 'nullable|integer|min:0',
//             'quantity'              => 'nullable|integer|min:0',
//             'barcode'               => 'nullable|string',
//             'time_prepare'          => 'nullable|date_format:H:i',
//             'bought_with'           => 'nullable|array',
//             'bought_with.*'         => 'nullable|integer|exists:products,id',
//             'is_instant_delivery'   => 'nullable|boolean',
//             // relations
//             'variants'                      => 'nullable|array',
//             'variants.*.attributes_values_ids' => 'required|array',

//             'category_details'              => 'nullable|array',
//             'category_details.*.category_detail_id' => 'required|exists:category_details,id',
//             'category_details.*.detail_value'       => 'required|array',

//             'extra_details'                 => 'nullable|array',
//             'extra_details.*.detail_key'    => 'required|array',
//             'extra_details.*.detail_value'  => 'required|array',

//             // media
//             'images' => 'nullable|array',
//             'images.*' => 'image|max:2048',
//         ];

//         foreach ($this->locales as $locale) {
//             $rules["name.$locale"] = 'required|string|max:255';
//             $rules["description.$locale"] = 'nullable|string';
//             $rules["full_description.$locale"] = 'nullable|string';
//             $rules["country.$locale"] = 'nullable|string';

//             $rules["category_details.*.detail_value.$locale"] = 'required|string';
//             $rules["extra_details.*.detail_key.$locale"] = 'required|string';
//             $rules["extra_details.*.detail_value.$locale"] = 'required|string';
//         }

//         return $rules;
//     }
// }

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;

class StoreRequest extends FormRequest
{
    protected array $locales = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->locales = Language::active()->pluck('code')->toArray();
        $data = $this->all();

        $translatableFields = [
            'name',
            'description',
            'full_description',
            'country',
        ];

        foreach ($translatableFields as $field) {
            $prepared = [];
            foreach ($this->locales as $locale) {
                if (isset($data[$field][$locale])) {
                    $prepared[$locale] = $data[$field][$locale];
                }
            }
            $this->merge([$field => $prepared]);
        }

        if (isset($data['category_details'])) {
            foreach ($data['category_details'] as $i => $cd) {
                foreach ($this->locales as $locale) {
                    if (!isset($cd['detail_value'][$locale])) {
                        $data['category_details'][$i]['detail_value'][$locale] = null;
                    }
                }
            }
            $this->merge(['category_details' => $data['category_details']]);
        }

        if (isset($data['extra_details'])) {
            foreach ($data['extra_details'] as $i => $ed) {
                foreach ($this->locales as $locale) {
                    if (!isset($ed['detail_key'][$locale])) {
                        $data['extra_details'][$i]['detail_key'][$locale] = null;
                    }
                    if (!isset($ed['detail_value'][$locale])) {
                        $data['extra_details'][$i]['detail_value'][$locale] = null;
                    }
                }
            }
            $this->merge(['extra_details' => $data['extra_details']]);
        }
    }

    public function rules(): array
    {
        $productId = $this->route('product'); 

        $rules = [
            'category_id'           => 'required|exists:categories,id',
            'sku'                   => 'nullable|string|unique:products,sku,' . $productId,
            'model'                 => 'nullable|string|unique:products,model,' . $productId,
            'price'                 => 'required|integer|min:0',
            'price_after_discount'  => 'nullable|integer|min:0',
            'quantity'              => 'nullable|integer|min:0',
            'barcode'               => 'nullable|string',
            'time_prepare'          => 'nullable|date_format:H:i',
            'bought_with'           => 'nullable|array',
            'bought_with.*'         => 'nullable|integer|exists:products,id',
            'is_instant_delivery'   => 'nullable|boolean',

            // Variants
            'variants'                      => 'nullable|array',
            'variants.*.id'                  => 'nullable|exists:product_variants,id',
            'variants.*.attributes_values_ids' => 'nullable|array',
            'variants.*.price'              => 'nullable|integer|min:0',
            'variants.*.sku'                => 'nullable|string',

            // Category Details
            'category_details'              => 'nullable|array',
            'category_details.*.id'         => 'nullable|exists:product_category_details,id',
            'category_details.*.category_detail_id' => 'nullable|exists:category_details,id',
            'category_details.*.detail_value'       => 'nullable|array',

            // Extra Details
            'extra_details'                 => 'nullable|array',
            'extra_details.*.id'            => 'nullable|exists:product_extra_details,id',
            'extra_details.*.detail_key'    => 'nullable|array',
            'extra_details.*.detail_value'  => 'nullable|array',

            // Media
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',

            // Shop Product Variants
            'shop_variants'                 => 'nullable|array',
            'shop_variants.*.id'            => 'nullable|exists:shop_product_variants,id',
            'shop_variants.*.shop_id'       => 'nullable|exists:shops,id',
            'shop_variants.*.variant_id'    => 'nullable|exists:product_variants,id',
            'shop_variants.*.price'         => 'nullable|integer|min:0',
        ];

        // Add locale-specific validation
        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'nullable|string|max:255';
            $rules["description.$locale"] = 'nullable|string';
            $rules["full_description.$locale"] = 'nullable|string';
            $rules["country.$locale"] = 'nullable|string';

            $rules["category_details.*.detail_value.$locale"] = 'nullable|string';
            $rules["extra_details.*.detail_key.$locale"] = 'nullable|string';
            $rules["extra_details.*.detail_value.$locale"] = 'nullable|string';
        }

        return $rules;
    }
}
