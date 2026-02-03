<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;

class UserRegisterRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',

            'phone' => 'nullable|string|required_without:email|regex:/^\d+$/',

            'email' => 'nullable|string|required_without:phone|email',

            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9]/',
            ],

            'city_id' => 'required|exists:cities,id',
            'governorate_id' => 'required|exists:governorates,id',

        ];
    }
}
