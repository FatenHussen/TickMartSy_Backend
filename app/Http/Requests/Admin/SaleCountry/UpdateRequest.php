<?php

namespace App\Http\Requests\Admin\SaleCountry;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;

class UpdateRequest extends FormRequest
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

        $prepared = [];
        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $prepared[$locale] = $data['name'][$locale];
            } else {
                $prepared[$locale] = null;
            }
        }
        $this->merge(['name' => $prepared]);
    }

    public function rules(): array
    {
        $rules = [
            'icon' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
