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
            'points.required' => 'عدد النقاط مطلوب / Points amount is required',
            'points.integer' => 'النقاط يجب أن تكون رقم صحيح / Points must be a valid number',
            'points.min' => 'النقاط يجب أن تكون على الأقل 1 / Points must be at least 1',
            'points.max' => 'النقاط لا يمكن أن تتجاوز 10,000 / Points cannot exceed 10,000',
            'reason.required' => 'سبب الاستبدال مطلوب / Reason for exchange is required',
            'reason.min' => 'السبب يجب أن يكون على الأقل 3 أحرف / Reason must be at least 3 characters',
            'reason.max' => 'السبب لا يمكن أن يتجاوز 255 حرف / Reason cannot exceed 255 characters',
            'reference_type.max' => 'نوع المرجع لا يمكن أن يتجاوز 50 حرف / Reference type cannot exceed 50 characters',
            'reference_id.integer' => 'معرف المرجع يجب أن يكون رقم صحيح / Reference ID must be a valid number',
            'reference_id.min' => 'معرف المرجع يجب أن يكون على الأقل 1 / Reference ID must be at least 1',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'points' => 'عدد النقاط / points amount',
            'reason' => 'سبب الاستبدال / exchange reason',
            'reference_type' => 'نوع المرجع / reference type',
            'reference_id' => 'معرف المرجع / reference ID',
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