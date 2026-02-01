<?php

namespace App\Http\Requests\Admin\SystemSetting;

use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array', 'min:1', 'max:50'],
            'settings.*.key' => ['required', 'string', 'exists:system_settings,key'],
            'settings.*.value' => ['required'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'settings.required' => 'Settings array is required',
            'settings.array' => 'Settings must be an array',
            'settings.min' => 'At least one setting is required',
            'settings.max' => 'Cannot update more than 50 settings at once',
            'settings.*.key.required' => 'Setting key is required',
            'settings.*.key.exists' => 'Setting key does not exist',
            'settings.*.value.required' => 'Setting value is required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'settings.*.key' => 'setting key',
            'settings.*.value' => 'setting value',
        ];
    }
}