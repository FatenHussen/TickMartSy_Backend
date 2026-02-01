<?php

namespace App\Http\Requests\Admin\Point;

use Illuminate\Foundation\Http\FormRequest;

class AddPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User is required',
            'user_id.exists' => 'Selected user does not exist',
            'points.required' => 'Points amount is required',
            'points.integer' => 'Points must be a number',
            'points.min' => 'Points must be at least 1',
            'reason.required' => 'Reason is required',
            'reason.max' => 'Reason must not exceed 255 characters',
        ];
    }
}