<?php

namespace App\Http\Requests\Admin\Category\CategoryDetail;

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

        $this->merge([
            'name' => $name,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'required|string|max:255';
        }

        return $rules;
    }
}