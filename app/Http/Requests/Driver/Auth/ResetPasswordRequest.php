<?php

namespace App\Http\Requests\Driver\Auth;

use App\Http\Requests\BaseRequest;

class ResetPasswordRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|same:new_password',
        ];
    }

    
}
