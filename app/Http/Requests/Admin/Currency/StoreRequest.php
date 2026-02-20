<?php

namespace App\Http\Requests\Admin\Currency;

use App\Http\Requests\BaseRequest;

class StoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:3', 'unique:currencies,code'],
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:100'],
            'name.ar' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:10'],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
