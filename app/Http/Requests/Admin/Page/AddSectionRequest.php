<?php

namespace App\Http\Requests\Admin\Page;

use App\Enums\VariantSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Unified payload that creates a Section and links it to a Page (PageSection)
 * in a single request, so the dashboard deals with one "add block" action.
 */
class AddSectionRequest extends FormRequest
{
    public const API_METHODS = [
        'brands',
        'categories',
        'recipes',
        'baskets',
        'schedule-basket',
        'products',
        'shops',
        'suggested_products',
        'suggested_baskets',
        'suggested_shops',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $manualTypes = array_keys(config('section_items'));

        return [
            // --- Section definition ---
            'type' => ['required', 'in:manual,api'],

            'name' => ['nullable', 'array'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],

            // manual
            'manual_model' => ['required_if:type,manual', 'nullable', Rule::in($manualTypes)],
            'item_ids' => ['required_if:type,manual', 'nullable', 'array', 'min:1'],
            'item_ids.*.item_id' => ['required_with:item_ids', 'integer'],
            'item_ids.*.link' => ['nullable', 'string', 'max:255'],
            'item_ids.*.order' => ['nullable', 'integer', 'min:0'],

            // api
            'api_method' => ['required_if:type,api', 'nullable', Rule::in(self::API_METHODS)],

            // --- Placement on the page (PageSection) ---
            'position' => ['nullable', 'in:before,after'],
            'order' => ['nullable', 'integer', 'min:1'],
            'variant' => ['nullable', Rule::in(VariantSection::values())],
            'background_color' => ['nullable', 'string', 'max:50'],
            'background_card_color' => ['nullable', 'string', 'max:50'],

            // Effective filters used to render API sections (category_id, type, ...).
            'filters' => ['nullable', 'array'],
            'filters.brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'filters.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filters.shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'filters.type' => ['nullable', 'string'],

            'show_when' => ['nullable', 'array'],
            'show_when.*' => ['nullable'],
        ];
    }
}
