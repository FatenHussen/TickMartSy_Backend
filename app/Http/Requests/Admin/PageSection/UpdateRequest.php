<?php

namespace App\Http\Requests\Admin\PageSection;

use App\Enums\VariantSection;
use App\Models\DisplayType;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRequest extends FormRequest
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

            'section_id' => ['nullable', 'integer', 'exists:sections,id'],

            '
            ' => ['nullable', 'integer', 'exists:pages,id'],

            'display_type_id' => ['nullable', 'integer', 'exists:display_types,id'],

            'position' => ['nullable', 'in:before,after'],
            'order' => ['nullable', 'integer'],
            'variant' => ['nullable', Rule::in(VariantSection::values())],

            'background_color' => ['nullable', 'string', 'max:50'],
            'background_card_color' => ['nullable', 'string', 'max:50'],

            'filters' => ['nullable', 'array'],
            'filters.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filters.min_price' => ['nullable', 'integer', 'min:1'],
            'filters.max_price' => ['nullable', 'integer', 'min:1'],
            'filters.price' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.en.required' => 'The English name is required.',
            'name.ar.required' => 'The Arabic name is required.',
            'section_id.required' => 'Section is required.',
            'section_id.exists' => 'Section not found.',
            'page_ids.required' => 'Pages are required.',
            'page_ids.*.item_id.exists' => 'Page item not found.',
            'display_type_id.required' => 'Display type is required.',
            'display_type_id.exists' => 'Display type not found.',
        ];
    }

    private function validateDisplayTypeAllowedPage(Validator $validator): void
    {
        $pageSection = $this->resolvePageSection();

        $displayTypeId = $this->filled('display_type_id')
            ? $this->integer('display_type_id')
            : $pageSection?->display_type_id;

        $pageId = $this->filled('page_id')
            ? $this->integer('page_id')
            : $pageSection?->page_id;

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

    private function resolvePageSection(): ?PageSection
    {
        $pageSection = $this->route('page_section')
            ?? $this->route('pageSection')
            ?? $this->route('pagesection');

        if ($pageSection instanceof PageSection) {
            return $pageSection;
        }

        if (is_numeric($pageSection)) {
            return PageSection::query()->find((int) $pageSection);
        }

        return null;
    }
}
