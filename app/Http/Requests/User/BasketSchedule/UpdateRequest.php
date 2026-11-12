<?php

namespace App\Http\Requests\User\BasketSchedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('user')->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],

            // 'category_id' => ['nullable', 'exists:categories,id'],

            'schedule_id' => ['nullable', 'exists:schedules,id'],

            'start_date' => ['nullable', 'date', 'after_or_equal:today'],

            'delivery_time' => ['nullable', 'date_format:H:i'],

            'is_active' => ['sometimes', 'boolean'],

            'items' => ['sometimes', 'array', 'min:1'],

            'items.*' => ['required', 'array'],
            'items.*.id' => ['nullable', 'integer', 'distinct'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.shop_product_variant_id' => ['required', 'integer', 'distinct', 'exists:shop_product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth('user')->id(),
            'is_active' => $this->is_active ?? true,
        ]);
    }
}
