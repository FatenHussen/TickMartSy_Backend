<?php

namespace App\Http\Requests\Admin\Basket\ScheduledBasket;

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
            'category_id' => 'nullable|integer|exists:categories,id',
            'name' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'schedule_id' => 'nullable|integer|exists:schedules,id',
        ];
    }
}
