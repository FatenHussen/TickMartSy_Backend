<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;

class UpdatePaswordRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|different:old_password',
            'new_password_confirmation' => 'required|same:new_password',
        ];
    }
}
