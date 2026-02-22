<?php

namespace App\Http\Requests\Admin\AdminNotification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'  => 'nullable|in:all,driver,user,vendor'
        ];
    }
}
