<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Log;

class VerifyUpdateRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'nullable|string|required_without:email',
            'email' => 'nullable|string|required_without:phone',
            'code' => 'required|string',
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
