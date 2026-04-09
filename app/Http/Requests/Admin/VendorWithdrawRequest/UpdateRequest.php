<?php

namespace App\Http\Requests\Admin\VendorWithdrawRequest;

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
            'status' => ['required', 'string', 'in:paid,rejected'],
            'payment_method' => ['nullable', 'string', 'in:bank_transfer,cash,wallet,other', 'required_if:status,paid'],
            'transfer_reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'rejection_reason' => ['nullable', 'string', 'max:1000', 'required_if:status,rejected'],
        ];
    }
}
