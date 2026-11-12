<?php

namespace App\Http\Requests\User\Cart;

use App\Enums\CartType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('user')->check();
    }

    public function rules(): array
    {
        return [
            'shop_product_variant_id' => ['required', 'integer', 'exists:shop_product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
            // "add" sums onto the existing line (product page); "set" pins the
            // quantity, so a repeated sync cannot inflate the cart.
            'mode' => ['nullable', Rule::in(['add', 'set'])],
            'extras' => ['nullable', 'array'],
            'extras.*.id' => ['required', 'integer', 'exists:product_extra_details,id'],
            'extras.*.quantity' => ['required', 'integer', 'min:1'],
            'cart_type' => ['nullable', Rule::in(array_column(CartType::cases(), 'value'))],
            'recipe_id' => ['nullable', 'integer', 'exists:recipes,id'],
            'admin_basket_id' => ['nullable', 'integer', 'exists:baskets,id'],
            'admin_schedule_basket_id' => ['nullable', 'integer', 'exists:baskets,id'],
            'basket_schedule_id' => ['nullable', 'integer', 'exists:basket_schedules,id'],
        ];
    }
}
