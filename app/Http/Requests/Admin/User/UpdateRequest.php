<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;



class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],

            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],

            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId)
            ],

            'password' => [
                'nullable',
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
                Rule::unique('users', 'affiliate_id')->ignore($userId)

            ],


            'affiliate_rate' => [
                'nullable',
                'numeric',
                'between:0,100'
            ],
        ];
    }
}
