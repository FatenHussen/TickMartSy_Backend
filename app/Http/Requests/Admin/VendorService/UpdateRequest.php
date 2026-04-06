<?php

namespace App\Http\Requests\Admin\VendorService;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'vendor_service_type_id' => 'sometimes|integer|exists:vendor_service_types,id',
            'name'                   => 'sometimes|array',
            'name.ar'                => 'nullable|string|max:255',
            'name.en'                => 'nullable|string|max:255',
            'description'            => 'nullable|array',
            'description.ar'         => 'nullable|string',
            'description.en'         => 'nullable|string',
            'is_active'              => 'nullable|boolean',
        ];
    }
}
