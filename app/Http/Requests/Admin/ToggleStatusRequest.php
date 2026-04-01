<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\ToggleStatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in(ToggleStatusService::allowedTypes())
            ],
            'id' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => __('custom.type_required'),
            'type.in' => __('custom.invalid_type'),
            'id.required' => __('custom.id_required'),
            'id.integer' => __('custom.id_must_be_integer'),
            'is_active.required' => __('custom.is_active_required'),
            'is_active.boolean' => __('custom.is_active_must_be_boolean'),
        ];
    }
}
