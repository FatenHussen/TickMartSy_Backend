<?php

namespace App\Http\Requests\Admin\Schedule;

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
            'is_active' => 'nullable|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'search' => 'nullable|string|max:255',
            'sort_field' => 'nullable|in:id,name,interval_days,discount_value,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ];
    }
}
