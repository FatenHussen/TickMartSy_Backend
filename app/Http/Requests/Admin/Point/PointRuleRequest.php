<?php

namespace App\Http\Requests\Admin\Point;

use Illuminate\Foundation\Http\FormRequest;

class PointRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ruleId = $this->route('id');

        return [
            'code' => 'required|string|max:50|unique:point_rules,code,' . $ruleId,
            'title' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'expires_after_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Rule code is required',
            'code.unique' => 'This rule code already exists',
            'title.required' => 'Rule title is required',
            'type.required' => 'Rule type is required',
            'type.in' => 'Rule type must be either fixed or percentage',
            'value.required' => 'Rule value is required',
            'value.min' => 'Rule value must be at least 1',
            'min_order_amount.min' => 'Minimum order amount must be at least 0',
            'expires_after_days.min' => 'Expiry days must be at least 1',
        ];
    }
}