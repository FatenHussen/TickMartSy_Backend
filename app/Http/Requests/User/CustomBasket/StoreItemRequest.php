<?php

namespace App\Http\Requests\User\CustomBasket;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }
}
