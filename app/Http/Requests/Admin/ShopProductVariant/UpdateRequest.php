<?php

namespace App\Http\Requests\Admin\ShopProductVariant;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'cost_price' => 'sometimes|numeric|min:0',
        ];
    }
}
