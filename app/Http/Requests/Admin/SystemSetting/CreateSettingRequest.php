<?php

namespace App\Http\Requests\Admin\SystemSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSettingRequest extends FormRequest
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
            'key' => ['required', 'string', 'max:100', 'unique:system_settings,key', 'regex:/^[a-z0-9_]+$/'],
            'value' => ['required'],
            'type' => ['required', Rule::in(['string', 'number', 'boolean', 'json'])],
            'group' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'key.required' => 'Setting key is required',
            'key.unique' => 'Setting key already exists',
            'key.regex' => 'Setting key can only contain lowercase letters, numbers, and underscores',
            'value.required' => 'Setting value is required',
            'type.required' => 'Setting type is required',
            'type.in' => 'Setting type must be one of: string, number, boolean, json',
            'group.required' => 'Setting group is required',
            'group.regex' => 'Setting group can only contain lowercase letters, numbers, and underscores',
            'title.required' => 'Setting title is required',
            'title.max' => 'Setting title cannot exceed 200 characters',
            'description.max' => 'Setting description cannot exceed 500 characters',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'key' => 'setting key',
            'value' => 'setting value',
            'type' => 'setting type',
            'group' => 'setting group',
            'title' => 'setting title',
            'description' => 'setting description',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert key and group to lowercase and replace spaces with underscores
        if ($this->has('key')) {
            $this->merge([
                'key' => strtolower(str_replace(' ', '_', trim($this->key))),
            ]);
        }

        if ($this->has('group')) {
            $this->merge([
                'group' => strtolower(str_replace(' ', '_', trim($this->group))),
            ]);
        }

        // Trim title and description
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->title),
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->description),
            ]);
        }
    }
}