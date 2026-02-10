<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_id' => [
                'nullable',
                'exists:areas,id'
            ],

            'is_affiliate' => ['nullable', 'boolean'],
            'affiliate_approved' => ['nullable', 'boolean'],

        ];
    }
}
