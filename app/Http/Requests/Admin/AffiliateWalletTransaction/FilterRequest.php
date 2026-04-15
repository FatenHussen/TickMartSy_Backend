<?php

namespace App\Http\Requests\Admin\AffiliateWalletTransaction;

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
            'type' => ['nullable', 'string', 'in:commission,visit_commission,withdraw'],
            'affiliate_id' => ['nullable', 'string', 'exists:users,affiliate_id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
