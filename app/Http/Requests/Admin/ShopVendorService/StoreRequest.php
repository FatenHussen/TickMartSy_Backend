<?php

namespace App\Http\Requests\Admin\ShopVendorService;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'shop_id'            => 'required|integer|exists:shops,id',
            'vendor_service_id'  => [
                'required',
                'integer',
                'exists:vendor_services,id',
                Rule::unique('shop_vendor_services', 'vendor_service_id')
                    ->where(fn ($query) => $query->where('shop_id', $this->input('shop_id'))),
            ],
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

    public function messages(): array
    {
        return [
            'vendor_service_id.unique' => 'This vendor service is already assigned to the selected shop.',
        ];
    }
}

