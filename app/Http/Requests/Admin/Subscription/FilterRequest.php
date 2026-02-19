<?php

namespace App\Http\Requests\Admin\Subscription;

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
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'status' => ['nullable', 'string', 'in:active,expired,cancelled'],
            'start_date_from' => ['nullable', 'date'],
            'start_date_to' => ['nullable', 'date', 'after_or_equal:start_date_from'],
            'search' => ['nullable', 'string', 'max:255'],
            'sortField' => ['nullable', 'string', 'in:id,start_date,end_date,created_at,status'],
            'sortOrder' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
