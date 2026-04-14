<?php

namespace App\Http\Requests\Admin\Badge;

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
            'name' => 'nullable|array',
            'name.en' => 'nullable|string',
            'name.ar' => 'nullable|string',
            'color' => 'nullable|string',
            'position' => 'required|in:top,bottom',
            'image' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,gif',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasName = $this->filled('name.en') || $this->filled('name.ar');
            $hasImage = $this->hasFile('image');

            if (! $hasName && ! $hasImage) {
                $validator->errors()->add('name', __('validation.required_without', [
                    'attribute' => 'name or image',
                    'values' => 'image',
                ]));
                $validator->errors()->add('image', __('validation.required_without', [
                    'attribute' => 'image or name',
                    'values' => 'name',
                ]));
            }
        });
    }
}
