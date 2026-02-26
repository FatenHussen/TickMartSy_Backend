<?php

namespace App\Http\Requests\Admin\VendorUser;

use App\Http\Requests\BaseRequest;

class StoreVendorUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:vendor_users,email',
            'password' => 'required|string|min:8',
            'vendor_id' => 'required|exists:vendors,id',
            'is_active' => 'boolean',
            'shop_ids' => 'array',
            'shop_ids.*' => 'exists:shops,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'vendor_id.required' => 'Vendor is required',
            'vendor_id.exists' => 'Selected vendor does not exist',
            'shop_ids.array' => 'Shop IDs must be an array',
            'shop_ids.*.exists' => 'One or more selected shops do not exist',
        ];
    }
}
