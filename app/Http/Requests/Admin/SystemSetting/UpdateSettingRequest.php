<?php

namespace App\Http\Requests\Admin\SystemSetting;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SystemSetting;

class UpdateSettingRequest extends FormRequest
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
        $setting = SystemSetting::where('key', $this->route('key'))->first();
        
        if (!$setting) {
            return ['value' => 'required'];
        }

        // Validate based on setting type
        return match ($setting->type) {
            'number' => [
                'value' => ['required', 'numeric', 'min:0', 'max:999999'],
            ],
            'boolean' => [
                'value' => ['required', 'boolean'],
            ],
            'json' => [
                'value' => ['required', 'json'],
            ],
            default => [
                'value' => ['required', 'string', 'max:1000'],
            ],
        };
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'value.required' => 'Setting value is required',
            'value.numeric' => 'Setting value must be a valid number',
            'value.boolean' => 'Setting value must be true or false',
            'value.json' => 'Setting value must be valid JSON',
            'value.string' => 'Setting value must be a string',
            'value.max' => 'Setting value cannot exceed 1000 characters',
            'value.min' => 'Setting value cannot be negative',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $setting = SystemSetting::where('key', $this->route('key'))->first();
        
        if ($setting && $setting->type === 'boolean') {
            // Convert string boolean to actual boolean
            $value = $this->value;
            if (is_string($value)) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($value !== null) {
                    $this->merge(['value' => $value]);
                }
            }
        }
    }
}