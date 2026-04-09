<?php

namespace App\Http\Requests\Admin\VendorWithdrawRequest;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:pending,paid,rejected'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'payment_method' => ['nullable', 'string', 'in:bank_transfer,cash,wallet,other'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
