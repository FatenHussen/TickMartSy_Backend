<?php

namespace App\Http\Requests\Driver\Auth;

use App\Http\Requests\BaseRequest;

class DriverLoginRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'phone' => 'required|string|exists:drivers,phone',
            'password' => 'required|string',

        ];
    }
}