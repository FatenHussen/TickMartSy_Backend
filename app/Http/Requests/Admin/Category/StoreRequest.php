<?php

namespace App\Http\Requests\Admin\Category;

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

        $name = [];
        $description = [];

        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $name[$locale] = $data['name'][$locale];
            }
            if (isset($data['description'][$locale])) {
                $description[$locale] = $data['description'][$locale];
            }
        }

        $this->merge([
            'name' => $name,
            'description' => $description,
        ]);
    }


    public function rules(): array
    {
        $rules = [
            'icon' => 'nullable|file',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.{$locale}"] = 'required|string|max:255';
            $rules["description.{$locale}"] = 'nullable|string';
        }

        return $rules;
    }


    protected function getLocaleName(string $locale): string
    {
        return match ($locale) {
            'ar' => 'العربية',
            'en' => 'الإنجليزية',
            default => $locale,
        };
    }
}
