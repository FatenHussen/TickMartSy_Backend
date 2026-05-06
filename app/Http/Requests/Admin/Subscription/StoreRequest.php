<?php

namespace App\Http\Requests\Admin\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'status' => ['nullable', 'string', 'in:pending,active,expired,cancelled'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'remaining_orders' => ['nullable', 'integer', 'min:0'],
            'remaining_free_deliveries' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
