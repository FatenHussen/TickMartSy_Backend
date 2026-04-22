<?php

namespace App\Http\Requests\Admin\PageSection;

use App\Enums\VariantSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'array'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],

            'section_id' => ['required', 'integer', 'exists:sections,id'],

            'page_id' => ['required', 'integer', 'exists:pages,id'],

            'display_type_id' => ['prohibited'],

            'position' => ['required', 'in:before,after'],
            'order' => ['required', 'integer', 'min:1'],
            'variant' => ['nullable', Rule::in(VariantSection::values())],

            'background_color' => ['nullable', 'string', 'max:50'],
            'background_card_color' => ['nullable', 'string', 'max:50'],

            'filters' => ['nullable', 'array'],
            'filters.brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'filters.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filters.shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            // 'filters.price_min' => ['nullable', 'integer', 'min:1'],
            // 'filters.price_max' => ['nullable', 'integer', 'min:1'],
            'filters.type' => ['nullable', 'string'],
        ];
    }
}
