<?php

namespace App\Http\Requests\Admin\PointExchange;

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
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'exchange_type' => ['nullable', 'string', 'in:coupon,free_delivery,gift'],
            'status' => ['nullable', 'string', 'in:pending,completed,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:255'],
            'sortField' => ['nullable', 'string', 'in:id,created_at,delivered_at,status'],
            'sortOrder' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
