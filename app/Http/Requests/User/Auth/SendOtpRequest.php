<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Log;

class SendOtpRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'phone' => 'nullable|string|exists:users,phone|required_without:email',
            'email' => 'nullable|string|exists:users,email|required_without:phone',
        ];
    }
    protected function prepareForValidation()
    {
        Log::info('Incoming VerifyPasswordRequest data:', $this->all());

        if ($this->has('phone')) {
            $cleanPhone = ltrim($this->input('phone'), '+');
            $this->merge([
                'phone' => $cleanPhone,
            ]);
        }
    }
}
