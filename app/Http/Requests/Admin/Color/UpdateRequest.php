<?php

namespace App\Http\Requests\Admin\Color;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('hex')) {
            $this->merge([
                'hex' => strtoupper((string) $this->input('hex')),
            ]);
        }
    }

    public function rules(): array
    {
        $locales = Language::active()->pluck('code')->toArray();
        $colorId = $this->route('color') ?? $this->route('id');

        $rules = [
            'hex' => [
                'nullable',
                'string',
                'regex:/^#[A-F0-9]{6}$/',
                Rule::unique('colors', 'hex')->ignore($colorId),
            ],
            'is_active' => 'nullable|boolean',
        ];

        foreach ($locales as $locale) {
            $rules["name.{$locale}"] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
