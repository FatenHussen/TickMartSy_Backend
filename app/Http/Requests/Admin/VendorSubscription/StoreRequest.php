<?php

namespace App\Http\Requests\Admin\VendorSubscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_id' => 'required|exists:shops,id',
            'vendor_package_id' => 'required|exists:vendor_packages,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'auto_renew' => 'boolean',
            'status' => 'in:active,pending,expired,cancelled',
            'notes' => 'nullable|string',
        ];
    }
}
