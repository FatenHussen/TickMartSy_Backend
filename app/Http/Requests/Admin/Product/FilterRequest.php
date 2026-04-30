<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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

        $attributeIds = array_merge(
            $this->normalizeIdInput($this->input('category_attribute_id')),
            $this->normalizeIdInput($this->input('category_attribute_ids'))
        );
        $attributeIds = array_values(array_unique(array_filter($attributeIds)));

        if (!empty($attributeIds)) {
            $this->merge([
                'category_attribute_id' => $attributeIds[0],
                'category_attribute_ids' => $attributeIds,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id'             => 'nullable|integer|exists:products,id',
            'shop_id'        => 'nullable|integer',
            'product_number' => 'nullable|string|max:255',
            'category_id'    => 'nullable|integer|exists:categories,id',
            'category_ids'   => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'brand_id'       => 'nullable|integer|exists:brands,id',
            'vendor_id'      => 'nullable|integer|exists:vendors,id',
            'category_attribute_id' => 'nullable|integer|exists:category_attributes,id',
            'category_attribute_ids' => 'nullable|array',
            'category_attribute_ids.*' => 'integer|exists:category_attributes,id',
            'stock_sort'     => 'nullable|in:asc,desc',
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
