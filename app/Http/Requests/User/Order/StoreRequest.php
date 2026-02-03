<?php

namespace App\Http\Requests\User\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address_id' => ['required', 'exists:user_addresses,id'],
            'cart_type' => ['nullable', 'string', 'in:default,recipe,admin_cart,schedule_admin_cart'],
            'is_instant_delivery' => ['required', 'boolean'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.shop_product_variant_id' => [
                'required',
                'exists:shop_product_variants,id'
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'recipe_id' => 'nullable|exists:recipes,id',
            'admin_basket_id' => 'nullable|exists:baskets,id',
            'coupon' => 'nullable|string',
            'affiliate_id' => 'nullable|string',
        ];
    }
}
