<?php

namespace App\Http\Requests\Admin\Section;

use App\Enums\SectionLayout;
use App\Enums\VariantSection;
use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create a reusable slider. No page is chosen here — the slider is attached
 * later from inside a page via "Add section".
 */
class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
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
        $allowedTypes = array_keys(config('section_items'));

        return [
            'name' => ['required', 'array'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],

            'content_type' => ['nullable', Rule::in(Section::CONTENT_TYPES)],
            'type' => ['required', 'in:manual,api'],

            'manual_model' => ['required_if:type,manual', 'nullable', Rule::in($allowedTypes)],
            'item_ids' => ['required_if:type,manual', 'nullable', 'array', 'min:1'],
            'item_ids.*.item_type' => ['required_with:item_ids', 'string'],
            'item_ids.*.item_id' => ['required_with:item_ids', 'integer'],
            'item_ids.*.link' => ['nullable', 'string', 'max:255'],
            'item_ids.*.order' => ['nullable', 'integer', 'min:0'],

            'api_method' => ['required_if:type,api', 'nullable', Rule::in(Section::API_METHODS)],

            'layout' => ['nullable', Rule::in(SectionLayout::values())],
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

            'see_more' => ['nullable', 'boolean'],
            'see_more_slug' => ['nullable', 'string', 'max:255'],
            'details_slug' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],

            // Sliders are not bound to a page at creation time.
            'page_id' => ['prohibited'],
            'page_ids' => ['prohibited'],
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
