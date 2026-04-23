<?php

namespace App\Http\Requests\Admin\ShopProductVariant;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $categoryIds = array_merge(
            $this->normalizeIdInput($this->input('category_id')),
            $this->normalizeIdInput($this->input('category_ids'))
        );

        $categoryIds = array_values(array_unique(array_filter($categoryIds)));

        if (!empty($categoryIds)) {
            $this->merge([
                'category_id' => $categoryIds[0],
                'category_ids' => $categoryIds,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'product_number' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'cost_price_min' => ['nullable', 'numeric', 'min:0'],
            'cost_price_max' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function normalizeIdInput(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return collect($value)
                ->flatMap(fn($item) => $this->normalizeIdInput($item))
                ->values()
                ->all();
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeIdInput($decoded);
            }

            if (str_contains($value, ',')) {
                return collect(explode(',', $value))
                    ->map(fn($item) => trim($item))
                    ->flatMap(fn($item) => $this->normalizeIdInput($item))
                    ->values()
                    ->all();
            }
        }

        if (is_numeric($value)) {
            return [(int) $value];
        }

        return [];
    }
}
