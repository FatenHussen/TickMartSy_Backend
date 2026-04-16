<?php

namespace App\Http\Requests\Admin\DriverWalletTransaction;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'in:paid_by_user,paid_by_system'],
            'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
