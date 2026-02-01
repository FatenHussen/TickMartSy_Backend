<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;

class UserLoginRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'phone' => 'nullable|string|exists:users,phone|required_without:email|regex:/^\\d+$/',
            'email' => 'nullable|string|exists:users,email|required_without:phone|regex:/@/',
            'password' => 'required|string',

        ];
    }
}