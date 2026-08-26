<?php

namespace App\Http\Requests\Admin\SaleCountry;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;

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

        $prepared = [];
        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $prepared[$locale] = $data['name'][$locale];
            }
        }
        $this->merge(['name' => $prepared]);
    }

    public function rules(): array
    {
        // icon is seeded (flag emoji); not uploaded from dashboard
        $rules = [
            'is_active' => 'nullable|boolean',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'required|string|max:255';
        }

        return $rules;
    }
}
