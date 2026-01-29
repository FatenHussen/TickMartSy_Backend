<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class VerifyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|size:5',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Verification code is required',
            'code.string' => 'Verification code must be a string',
            'code.size' => 'Verification code must be exactly 5 digits',
        ];
    }
}