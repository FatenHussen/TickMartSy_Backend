<?php

namespace App\Http\Requests\Admin\Promotion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|array',
            'name.en' => 'nullable|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'type' => ['nullable', Rule::in([
                'simple_discount',
                'spend_x_discount',
                'spend_x_get_gift',
                'spend_x_get_points',
                'free_shipping',
            ])],
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'min_spend' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(fn () => in_array(
                    $this->input('type'),
                    ['spend_x_discount', 'spend_x_get_gift', 'spend_x_get_points'],
                    true
                )),
            ],
            'discount_value' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'gift_description' => ['nullable', 'array'],
            'gift_description.en' => [
                Rule::requiredIf(fn () => $this->input('type') === 'spend_x_get_gift'),
                'string',
            ],
            'gift_description.ar' => [
                Rule::requiredIf(fn () => $this->input('type') === 'spend_x_get_gift'),
                'string',
            ],
            'reward_points' => [
                'nullable',
                'integer',
                'min:1',
                Rule::requiredIf(fn () => $this->input('type') === 'spend_x_get_points'),
            ],
        ];
    }
}
