<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Category;
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
                if (!isset($data['extra_details'][$i]['product_extra_detail_id'])) {
                    continue;
                }
            }
            $this->merge(['extra_details' => $data['extra_details']]);
        }
        if (auth('vendor-user')->check()) {
            $this->merge([
                'sale_channel' => 'shop',
                'vendor_id' => auth('vendor-user')->user()->id ?? 1,
            ]);
        } else {
            $channel = $this->input('sale_channel', 'platform');
            if (!in_array($channel, ['platform', 'shop'], true)) {
                $channel = 'platform';
            }
            $merge = ['sale_channel' => $channel];
            if ($channel === 'platform') {
                $merge['vendor_id'] = 1;
            } elseif (!$this->filled('vendor_id')) {
                $merge['vendor_id'] = 1;
            }
            $this->merge($merge);
        }

        $this->normalizeSypPriceInputs();

        $this->normalizeRestrictedFieldsForRestaurantCategory();
    }

    private function normalizeSypPriceInputs(): void
    {
        $normalized = \App\Helpers\CurrencyHelper::applySypPriceInputs($this->all());

        $merge = [];
        if (array_key_exists('price', $normalized)) {
            $merge['price'] = $normalized['price'];
        }
        if (array_key_exists('cost_price', $normalized)) {
            $merge['cost_price'] = $normalized['cost_price'];
        }
        if (array_key_exists('variants', $normalized)) {
            $merge['variants'] = $normalized['variants'];
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    private function normalizeRestrictedFieldsForRestaurantCategory(): void
    {
        $categoryId = $this->input('category_id');

        if (!$categoryId) {
            return;
        }

        $isRestaurant = Category::query()
            ->whereKey($categoryId)
            ->value('is_restaurant');

        if (!$isRestaurant) {
            return;
        }

        $this->merge([
            'country_id' => null,
            'sale_country_id' => null,
            'sku' => null,
            'model' => null,
            'barcode' => null,
            'brand_id' => null,
            'country' => null,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'category_id'           => 'required|exists:categories,id',
            'product_number'        => 'nullable|string|max:255|unique:products,product_number',
            'sku'                   => 'nullable|string|unique:products,sku',
            'model'                 => 'nullable|string|unique:products,model',
            'country_id'            => 'nullable|exists:countries,id',
            'sale_country_id'       => 'nullable|exists:sale_countries,id',
            'price'                 => 'nullable|numeric|min:0',
            'cost_price'            => 'nullable|numeric|min:0',
            'discount'              => 'nullable|integer|min:0|max:100',
            'discount_type'         => 'nullable|in:none,percentage,fixed',
            'quantity'              => 'nullable|integer|min:0',
            'unit'                  => 'nullable|string|max:50',
            'unit_id'               => 'nullable|integer|exists:units,id',
            'warranty_period'       => 'nullable|integer|min:0',
            'stock'                 => 'nullable|integer|min:0',
            'max_purchase_quantity' => 'nullable|integer|min:1',
            'barcode'               => 'nullable|string',
            'time_prepare'          => 'nullable|string',
            'delivery_time'         => 'nullable|string|max:100',
            'expiry_date'           => 'nullable|date',
            'bought_with'           => 'nullable|array',
            'bought_with.*'         => 'nullable|integer|exists:products,id',
            'is_instant_delivery'   => 'nullable|boolean',
            'is_visible'            => 'nullable|boolean',
            'thumbnail'             => 'nullable|image',
            'sale_channel'          => 'nullable|in:platform,shop',
            'vendor_id'             => 'nullable|integer|exists:vendors,id',

            // Variants
            'variants'                      => 'nullable|array',
            'variants.*.name'               => 'nullable|array',
            'variants.*.name.ar'            => 'nullable|string|max:255',
            'variants.*.name.en'            => 'nullable|string|max:255',
            'variants.*.sku'                => 'nullable|string|unique:product_variants,sku',
            'variants.*.model'              => 'nullable|string|max:255',
            'variants.*.barcode'            => 'nullable|string|max:255',
            'variants.*.price'              => 'nullable|numeric|min:0',
            'variants.*.discount'           => 'nullable|integer|min:0|max:100',
            'variants.*.discount_type'      => 'nullable|in:none,percentage,fixed',
            'variants.*.quantity'           => 'nullable|integer|min:0',
            'variants.*.is_trend'           => 'nullable|boolean',
            'variants.*.is_active'          => 'nullable|boolean',
            'variants.*.attributes_values_ids' => 'nullable|array',
            'variants.*.attributes_values_ids.*' => 'required|integer|exists:attribute_values,id',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'nullable|image',

            // Category Details
            'category_details'              => 'nullable|array',
            'category_details.*.category_detail_id' => 'nullable|exists:category_details,id',
            'category_details.*.detail_value'       => 'nullable|array',

            // Extra Details (select from existing pool)
            'extra_details'                       => 'nullable|array',
            'extra_details.*.product_extra_detail_id' => 'required|exists:product_extra_details,id',
            'extra_details.*.quantity'            => 'required|integer|min:0',
            'extra_details.*.price'               => 'required|numeric|min:0',

            // Media (optional — product can be created without images)
            'media' => 'nullable|array',
            'media.*' => 'nullable|image|max:5120',

            // Shop Product Variants — required when sale_channel=shop (see withValidator)
            'shop_variants'                     => 'nullable|array',
            'shop_variants.*.shop_id'           => 'required|exists:shops,id',
            'shop_variants.*.variant_index'     => 'required|integer|min:0',
            'shop_variants.*.cost_price'        => 'nullable|numeric|min:0',
            'brand_id' => 'nullable|integer|exists:brands,id',


            'badges'          => 'nullable|array',
            'badges.*' => 'integer|exists:badges,id',

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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('sale_channel') !== 'shop') {
                return;
            }

            $shopVariants = $this->input('shop_variants');
            if (!is_array($shopVariants) || count($shopVariants) < 1) {
                $validator->errors()->add(
                    'shop_variants',
                    'عند اختيار «ربط بمتجر» يجب اختيار فرع واحد على الأقل.'
                );
            }
        });
    }
}
