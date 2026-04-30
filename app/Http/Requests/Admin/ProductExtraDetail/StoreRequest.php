<?php

namespace App\Http\Requests\Admin\ProductExtraDetail;

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

        $detailKey = [];
        foreach ($this->locales as $locale) {
            if (isset($data['detail_key'][$locale])) {
                $detailKey[$locale] = $data['detail_key'][$locale];
            }
        }

        $detailValue = [];
        foreach ($this->locales as $locale) {
            if (isset($data['detail_value'][$locale])) {
                $detailValue[$locale] = $data['detail_value'][$locale];
            }
        }

        $this->merge([
            'detail_key' => $detailKey,
            'detail_value' => $detailValue,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'detail_key' => 'required|array',
            'detail_value' => 'required|array',
            'is_active' => 'nullable|boolean',
        ];

        foreach ($this->locales as $locale) {
            $rules["detail_key.$locale"] = 'nullable|string|max:255';
            $rules["detail_value.$locale"] = 'nullable|string';
        }

        return $rules;
    }
}
