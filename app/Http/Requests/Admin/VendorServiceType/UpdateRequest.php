<?php

namespace App\Http\Requests\Admin\VendorServiceType;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'      => 'sometimes|array',
            'name.ar'   => 'nullable|string|max:255',
            'name.en'   => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
