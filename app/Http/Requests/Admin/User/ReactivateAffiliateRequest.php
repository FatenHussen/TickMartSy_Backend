<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReactivateAffiliateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'affiliate_id' => ['nullable', 'string'],
            'affiliate_commission_type' => [
                'required',
                Rule::in([
                    'percentage_order',
                    'fixed_per_order',
                    'percentage_selected_products',
                ]),
            ],
            'affiliate_rate' => ['nullable', 'numeric', 'between:0,100'],
            'affiliate_fixed_commission' => ['nullable', 'numeric', 'min:0'],
            'affiliate_product_ids' => ['nullable', 'array'],
            'affiliate_product_ids.*' => ['integer', 'exists:products,id'],
            'affiliate_visit_commission_enabled' => ['nullable', 'boolean'],
            'affiliate_visit_commission_threshold' => ['nullable', 'integer', 'min:1'],
            'affiliate_visit_commission_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

