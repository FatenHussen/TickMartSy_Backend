<?php

namespace App\Http\Requests\Driver\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Log;

class VerifyPasswordRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'phone' => 'required|string|exists:drivers,phone',
            'code' => 'required|string',
        ];
    }
}
