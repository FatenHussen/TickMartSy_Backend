<?php

namespace App\Http\Requests\User\Currency;

use App\Http\Requests\BaseRequest;

class UpdateUserCurrencyRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_id' => ['required', 'exists:currencies,id'],
        ];
    }
}
