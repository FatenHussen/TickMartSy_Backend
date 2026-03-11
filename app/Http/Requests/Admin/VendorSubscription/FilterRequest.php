<?php

namespace App\Http\Requests\Admin\VendorSubscription;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => 'nullable|exists:vendors,id',
            'vendor_package_id' => 'nullable|exists:vendor_packages,id',
            'status' => 'nullable|in:active,pending,expired,cancelled',
            'expiring_soon' => 'nullable|boolean',
        ];
    }
}
