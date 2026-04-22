<?php

namespace App\Http\Requests\Admin\AdminNotification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required',
            'body'  => 'required',
            'type'  => 'nullable|required_without:types|in:all,driver,user,vendor',
            'types' => 'nullable|required_without:type|array|min:1',
            'types.*' => 'required|string|in:all,driver,user,vendor',
            'channels' => 'required|array|min:1',
            'channels.*' => 'required|string|in:fcm,sms,email',
            'driver_ids' => 'nullable|array',
            'driver_ids.*' => 'integer|exists:drivers,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'vendor_ids' => 'nullable|array',
            'vendor_ids.*' => 'integer|exists:vendor_users,id',
            'target_page' => [
                'nullable',
                'string',
                Rule::exists('pages', 'slug'),
            ],
            'emoji' => 'nullable|string|max:10',
            'media' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp',
        ];
    }
}
