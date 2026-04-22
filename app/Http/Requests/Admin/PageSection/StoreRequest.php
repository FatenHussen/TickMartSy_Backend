<?php

namespace App\Http\Requests\Admin\PageSection;

use App\Enums\VariantSection;
use App\Models\DisplayType;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRequest extends FormRequest
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateDisplayTypeAllowedPage($validator);
        });
    }

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

            'display_type_id' => ['required', 'integer', 'exists:display_types,id'],

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

    private function validateDisplayTypeAllowedPage(Validator $validator): void
    {
        $displayTypeId = $this->integer('display_type_id');
        $pageId = $this->integer('page_id');

        if (!$displayTypeId || !$pageId) {
            return;
        }

        $displayType = DisplayType::query()->find($displayTypeId);
        $page = Page::query()->find($pageId);

        if (!$displayType || !$page) {
            return;
        }

        $allowedPageSlugs = $displayType->allowed_page_slugs ?? [];

        if (!empty($allowedPageSlugs) && !in_array($page->slug, $allowedPageSlugs, true)) {
            $validator->errors()->add(
                'display_type_id',
                __('The selected display type is not allowed for this page.')
            );
        }
    }
}
