<?php

namespace App\Http\Requests\Admin\Color;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    protected array $locales = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->locales = Language::active()->pluck('code')->toArray();
        $data = $this->all();
        $name = [];

        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $name[$locale] = $data['name'][$locale];
            }
        }

        $payload = ['name' => $name];

        if ($this->filled('hex')) {
            $payload['hex'] = strtoupper((string) $this->input('hex'));
        }

        $this->merge($payload);
    }

    public function rules(): array
    {
        $rules = [
            'hex' => ['required', 'string', 'regex:/^#[A-F0-9]{6}$/', 'unique:colors,hex'],
            'is_active' => 'nullable|boolean',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.{$locale}"] = 'required|string|max:255';
        }

        return $rules;
    }
}
