<?php

namespace App\Http\Requests\Admin\Category\CategoryAttribute;

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

        // attribute name
        $name = [];
        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $name[$locale] = $data['name'][$locale];
            }
        }

        // values names
        $values = [];
        if (!empty($data['values'])) {
            foreach ($data['values'] as $value) {
                $valueName = [];
                foreach ($this->locales as $locale) {
                    if (isset($value['name'][$locale])) {
                        $valueName[$locale] = $value['name'][$locale];
                    }
                }
                $values[] = [
                    'name' => $valueName,
                ];
            }
        }

        $this->merge([
            'name'   => $name,
            'values' => $values,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:square,circle,color',
            'values'      => 'nullable|array|min:1',
            'values.*.name' => 'required|array',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'required|string|max:255';
            $rules["values.*.name.$locale"] = 'required|string|max:255';
        }

        return $rules;
    }
}
