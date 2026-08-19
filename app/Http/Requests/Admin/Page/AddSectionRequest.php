<?php

namespace App\Http\Requests\Admin\Page;

use App\Enums\VariantSection;
use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Attach an existing slider from the library to a page,
 * or create a new one inline (legacy unified flow).
 */
class AddSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('section_id')) {
            return;
        }

        $this->merge($this->normalizeContentType($this->all()));

        $manualModel = $this->input('manual_model');
        $typeConfig = $manualModel ? config("section_items.$manualModel") : null;

        if ($typeConfig && is_array($this->input('item_ids'))) {
            $itemIds = $this->input('item_ids', []);
            foreach ($itemIds as &$item) {
                $item['item_type'] = $typeConfig['item_type'];
            }
            $this->merge(['item_ids' => $itemIds]);
        }
    }

    public function rules(): array
    {
        $manualTypes = array_keys(config('section_items'));

        return [
            // --- Attach existing slider from library ---
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],

            // --- Or create inline (legacy) ---
            'content_type' => ['nullable', Rule::in(Section::CONTENT_TYPES)],
            'type' => ['required_without:section_id', 'nullable', 'in:manual,api'],

            'name' => ['nullable', 'array'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],

            'manual_model' => ['required_if:type,manual', 'nullable', Rule::in($manualTypes)],
            'item_ids' => ['required_if:type,manual', 'nullable', 'array', 'min:1'],
            'item_ids.*.item_id' => ['required_with:item_ids', 'integer'],
            'item_ids.*.link' => ['nullable', 'string', 'max:255'],
            'item_ids.*.order' => ['nullable', 'integer', 'min:0'],

            'api_method' => ['required_if:type,api', 'nullable', Rule::in(Section::API_METHODS)],

            // --- Placement on the page ---
            'position' => ['nullable', 'in:before,after'],
            'order' => ['nullable', 'integer', 'min:1'],
            'variant' => ['nullable', Rule::in(VariantSection::values())],
            'background_color' => ['nullable', 'string', 'max:50'],
            'background_card_color' => ['nullable', 'string', 'max:50'],

            'filters' => ['nullable', 'array'],
            'filters.brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'filters.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filters.shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'filters.parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filters.type' => ['nullable', 'string'],
            'filters.is_restaurant' => ['nullable', 'boolean'],

            'show_when' => ['nullable', 'array'],
            'show_when.*' => ['nullable'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeContentType(array $data): array
    {
        $contentType = $data['content_type'] ?? null;
        if (!$contentType) {
            return [];
        }

        $type = $data['type'] ?? (empty($data['item_ids']) ? 'api' : 'manual');
        $merged = ['type' => $type];

        if ($type === 'manual') {
            $merged['manual_model'] = $data['manual_model'] ?? $contentType;
        } else {
            $merged['api_method'] = $data['api_method']
                ?? (Section::API_METHOD_BY_CONTENT[$contentType] ?? null);

            if ($contentType === 'restaurant') {
                $filters = $data['filters'] ?? [];
                $filters['is_restaurant'] = true;
                $merged['filters'] = $filters;
            }
        }

        return $merged;
    }
}
