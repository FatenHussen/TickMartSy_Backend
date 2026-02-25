<?php

namespace App\Http\Requests\Admin\SellerRegistration;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'contract_duration_months' => 'nullable|integer|min:1',
        ];
    }

    public function attributes(): array
    {
        return [
            'commission_rate' => 'نسبة العمولة',
            'contract_duration_months' => 'مدة العقد بالأشهر',
        ];
    }
}
