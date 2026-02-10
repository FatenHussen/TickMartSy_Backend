<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:users,email'
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                'unique:users,phone'
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
            ],

            'area_id' => [
                'nullable',
                'exists:areas,id'
            ],

            'image' => [
                'nullable',
                'image',
                'max:2048'
            ],

            /* ========= Marketer ========= */

            'is_affiliate' => ['nullable', 'boolean'],
            'affiliate_approved' => ['nullable', 'boolean'],

            'affiliate_id' => [
                'nullable',
                'unique:users,affiliate_id'

            ],

            'affiliate_rate' => [
                'nullable',
                'numeric',
                'between:0,100'
            ],
        ];
    }
}
