<?php

namespace App\Http\Requests\Admin\VendorServiceType;

use App\Http\Requests\BaseRequest;

class StoreRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'      => 'required|array',
            'name.ar'   => 'required|string|max:255',
            'name.en'   => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
