<?php

namespace App\Http\Requests\Admin\VendorService;

use App\Http\Requests\BaseRequest;

class StoreRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'vendor_service_type_id' => 'required|integer|exists:vendor_service_types,id',
            'name'                   => 'required|array',
            'name.ar'                => 'required|string|max:255',
            'name.en'                => 'nullable|string|max:255',
            'description'            => 'nullable|array',
            'description.ar'         => 'nullable|string',
            'description.en'         => 'nullable|string',
            'is_active'              => 'nullable|boolean',
        ];
    }
}
