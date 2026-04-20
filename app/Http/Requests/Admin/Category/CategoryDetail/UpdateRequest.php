<?php

namespace App\Http\Requests\Admin\Category\CategoryDetail;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

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

        $name = [];
        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $name[$locale] = $data['name'][$locale];
            }
        }

        $valueOptions = [];
        if (isset($data['value_options']) && is_array($data['value_options'])) {
            foreach ($data['value_options'] as $option) {
                if (!is_array($option)) {
                    continue;
                }

                $preparedOption = [];
                $hasAnyValue = false;

                foreach ($this->locales as $locale) {
                    $value = $option[$locale] ?? null;

                    if (is_string($value)) {
                        $value = trim($value);
                        $value = $value === '' ? null : $value;
                    }

                    if ($value !== null) {
                        $hasAnyValue = true;
                    }

                    $preparedOption[$locale] = $value;
                }

                if ($hasAnyValue) {
                    $valueOptions[] = $preparedOption;
                }
            }
        }

        $this->merge([
            'name' => $name,
            'value_options' => $valueOptions,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'value_options' => 'nullable|array',
            'value_options.*' => 'nullable|array',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'required|string|max:255';
            $rules["value_options.*.$locale"] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
