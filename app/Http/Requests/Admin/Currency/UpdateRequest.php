<?php

namespace App\Http\Requests\Admin\Currency;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currencyId = $this->route('currency');

        return [
            'code' => ['sometimes', 'string', 'size:3', Rule::unique('currencies', 'code')->ignore($currencyId)],
            'name' => ['sometimes', 'array'],
            'name.en' => ['sometimes', 'string', 'max:100'],
            'name.ar' => ['sometimes', 'string', 'max:100'],
            'symbol' => ['sometimes', 'string', 'max:10'],
            'exchange_rate' => ['sometimes', 'numeric', 'min:0.000001'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
