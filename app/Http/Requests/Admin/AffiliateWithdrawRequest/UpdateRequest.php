<?php

namespace App\Http\Requests\Admin\AffiliateWithdrawRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:approved,rejected'],
            'note' => ['nullable', 'string', 'max:1000', 'required_if:status,rejected'],
        ];
    }
}
