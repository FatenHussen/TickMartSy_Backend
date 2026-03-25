<?php

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
            'seo_title',
            'seo_description',
            'seo_keywords',
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
        $this->merge([
            'vendor_id' => auth('vendor-user')->user()->id ?? 1,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'category_id'           => 'required|exists:categories,id',
            'sku'                   => 'nullable|string|unique:products,sku',
            'model'                 => 'nullable|string|unique:products,model',
            'price'                 => 'required|integer|min:0',
            'cost_price'            => 'nullable|numeric|min:0',
            'discount'              => 'nullable|integer|min:0|max:100',
            'discount_type'         => 'nullable|in:none,percentage,fixed',
            'quantity'              => 'nullable|integer|min:0',
            'unit'                  => 'nullable|string|max:50',
            'warranty_period'       => 'nullable|integer|min:0',
            'barcode'               => 'nullable|string',
            'time_prepare'          => 'nullable|string',
            'bought_with'           => 'nullable|array',
            'bought_with.*'         => 'nullable|integer|exists:products,id',
            'is_instant_delivery'   => 'nullable|boolean',
            'is_visible'            => 'nullable|boolean',
            'thumbnail'             => 'nullable|image',

            // Variants
            'variants'                      => 'nullable|array',
            'variants.*.attributes_values_ids' => 'nullable|array',
            'variants.*.attributes_values_ids.*' => 'required|integer|exists:attribute_values,id',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'nullable|image',

            // Category Details
            'category_details'              => 'nullable|array',
            'category_details.*.category_detail_id' => 'nullable|exists:category_details,id',
            'category_details.*.detail_value'       => 'nullable|array',

            // Extra Details
            'extra_details'                 => 'nullable|array',
            'extra_details.*.detail_key'    => 'nullable|array',
            'extra_details.*.detail_value'  => 'nullable|array',
            'extra_details.*.price'         => 'nullable|numeric|min:0',

            // Media
            'media' => 'required|array|min:1',
            'media.*' => 'required|image|max:5120',

            // Shop Product Variants
            // Shop Product Variants
            'shop_variants'                     => 'nullable|array',
            'shop_variants.*.shop_id'           => 'required|exists:shops,id',
            'shop_variants.*.variant_index'     => 'required|integer|min:0',
            'shop_variants.*.price'             => 'nullable|integer|min:0',
            'shop_variants.*.quantity'          => 'nullable|integer|min:0',
            'brand_id' => 'nullable|integer|exists:brands,id',


            'badges'          => 'nullable|array',
            'badges.*.id'  => 'required|integer|exists:badges,id',
            'badges.*.position'  => 'required|in:top,bottom',

            'icon_ids' => 'nullable|array',
            'icon_ids.*' => 'required|integer|exists:icons,id',

            // SEO Fields
            'seo_image' => 'nullable|image',

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

            // SEO Fields
            $rules["seo_title.$locale"] = 'nullable|string|max:160';
            $rules["seo_description.$locale"] = 'nullable|string|max:320';
            $rules["seo_keywords.$locale"] = 'nullable|array';
        }

        return $rules;
    }
}
