<?php

namespace App\Http\Requests\Admin\ProductExtraDetail;

use App\Models\Language;
use App\Models\ProductExtraDetail;
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

        $existing = null;
        $routeDetail = $this->route('product_extra_detail') ?? $this->route('product-extra-detail') ?? $this->route('productExtraDetail');

        if ($routeDetail instanceof ProductExtraDetail) {
            $existing = $routeDetail;
        } elseif (is_numeric($routeDetail)) {
            $existing = ProductExtraDetail::query()->find((int) $routeDetail);
        }

        $detailKey = [];
        $existingKeyTranslations = $existing ? ($existing->getTranslations('detail_key') ?? []) : [];
        foreach ($this->locales as $locale) {
            if (array_key_exists($locale, $data['detail_key'] ?? [])) {
                $detailKey[$locale] = $data['detail_key'][$locale];
            } elseif (array_key_exists($locale, $existingKeyTranslations)) {
                $detailKey[$locale] = $existingKeyTranslations[$locale];
            }
        }

        $detailValue = [];
        $existingValueTranslations = $existing ? ($existing->getTranslations('detail_value') ?? []) : [];
        foreach ($this->locales as $locale) {
            if (array_key_exists($locale, $data['detail_value'] ?? [])) {
                $detailValue[$locale] = $data['detail_value'][$locale];
            } elseif (array_key_exists($locale, $existingValueTranslations)) {
                $detailValue[$locale] = $existingValueTranslations[$locale];
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
