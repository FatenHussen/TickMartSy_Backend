<?php

namespace App\Http\Requests\Admin\ShopVendorService;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'extra_details'      => 'nullable|array',
            'price'              => 'nullable|numeric|min:0',
            'price_unit'         => 'nullable|string|max:100',
            'duration_minutes'   => 'nullable|integer|min:1',
            'schedule'           => 'nullable|array',
            'schedule.*.open'    => 'nullable|string',
            'schedule.*.close'   => 'nullable|string',
            'schedule.*.closed'  => 'nullable|boolean',
            'is_active'          => 'nullable|boolean',
        ];
    }
}
