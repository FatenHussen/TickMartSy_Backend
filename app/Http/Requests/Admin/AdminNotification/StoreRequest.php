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
            'type'  => 'required|in:all,driver,user,vendor',
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
