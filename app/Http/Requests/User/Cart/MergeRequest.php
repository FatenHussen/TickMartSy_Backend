<?php

namespace App\Http\Requests\User\Cart;

use App\Enums\CartType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('user')->check();
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.shop_product_variant_id' => ['required', 'integer', 'exists:shop_product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'items.*.extras' => ['nullable', 'array'],
            'items.*.extras.*.id' => ['required', 'integer', 'exists:product_extra_details,id'],
            'items.*.extras.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.cart_type' => ['nullable', Rule::in(array_column(CartType::cases(), 'value'))],
            'items.*.recipe_id' => ['nullable', 'integer', 'exists:recipes,id'],
            'items.*.admin_basket_id' => ['nullable', 'integer', 'exists:baskets,id'],
            'items.*.admin_schedule_basket_id' => ['nullable', 'integer', 'exists:baskets,id'],
            'items.*.basket_schedule_id' => ['nullable', 'integer', 'exists:basket_schedules,id'],
        ];
    }
}
