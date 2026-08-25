<?php

namespace App\Http\Requests\Admin\CustomOrderRequest;

use App\Enums\PriceVarianceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ConvertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string', Rule::in(['catalog', 'external'])],
            'items.*.shop_product_variant_id' => [
                'required_if:items.*.type,catalog',
                'nullable',
                'integer',
                'exists:shop_product_variants,id',
            ],
            'items.*.product_name' => [
                'required_if:items.*.type,external',
                'nullable',
                'string',
                'max:255',
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => [
                'required_if:items.*.type,external',
                'nullable',
                'numeric',
                'min:0',
            ],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'items.*.invoice_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'delivery_price' => ['nullable', 'numeric', 'min:0'],
            'approximate_total' => ['nullable', 'numeric', 'min:0'],
            'price_variance_type' => [
                'nullable',
                'string',
                Rule::in(array_column(PriceVarianceType::cases(), 'value')),
            ],
            'price_variance_value' => ['nullable', 'numeric', 'min:0'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'is_instant_delivery' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);
            $hasExternal = collect($items)->contains(fn ($item) => ($item['type'] ?? null) === 'external');

            if ($hasExternal) {
                if (! $this->filled('price_variance_type')) {
                    $validator->errors()->add(
                        'price_variance_type',
                        __('custom.custom_order_requests.variance_required_for_external')
                    );
                }
                if (! $this->filled('price_variance_value')) {
                    $validator->errors()->add(
                        'price_variance_value',
                        __('custom.custom_order_requests.variance_required_for_external')
                    );
                }
            }
        });
    }
}
