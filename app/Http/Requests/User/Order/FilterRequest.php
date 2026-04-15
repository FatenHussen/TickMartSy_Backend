<?php

namespace App\Http\Requests\User\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(OrderStatus::cases())],
            'is_restaurant' => ['nullable', 'boolean'],
            'shop_type' => ['nullable', 'in:restaurant,store'],
        ];
    }
}
