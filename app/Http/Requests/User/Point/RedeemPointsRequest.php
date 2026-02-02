<?php

namespace App\Http\Requests\User\Point;

use Illuminate\Foundation\Http\FormRequest;

class RedeemPointsRequest extends FormRequest
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
            'points' => ['required', 'integer', 'min:1', 'max:10000'],
            'reason' => ['required', 'string', 'max:255', 'min:3'],
            'reference_type' => ['nullable', 'string', 'max:50'],
            'reference_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'points.required' => 'Points amount is required',
            'points.integer' => 'Points must be a valid number',
            'points.min' => 'Points must be at least 1',
            'points.max' => 'Points cannot exceed 10,000',
            'reason.required' => 'Reason for redemption is required',
            'reason.min' => 'Reason must be at least 3 characters',
            'reason.max' => 'Reason cannot exceed 255 characters',
            'reference_type.max' => 'Reference type cannot exceed 50 characters',
            'reference_id.integer' => 'Reference ID must be a valid number',
            'reference_id.min' => 'Reference ID must be at least 1',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'points' => 'points amount',
            'reason' => 'redemption reason',
            'reference_type' => 'reference type',
            'reference_id' => 'reference ID',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from reason
        if ($this->has('reason')) {
            $this->merge([
                'reason' => trim($this->reason),
            ]);
        }
    }
}