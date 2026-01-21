<?php

namespace App\Http\Requests\User\Cart;

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
            'address_id' => ['nullable', 'exists:user_addresses,id'],
            'items'      => ['required', 'array', 'min:1'],
            'items.*'    => ['required', 'integer', 'exists:shop_product_variants,id'],
        ];
    }
}
