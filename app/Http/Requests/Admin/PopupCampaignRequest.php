<?php

namespace App\Http\Requests\Admin;

use App\Models\PopupCampaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class PopupCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $campaign = $this->route('popup_campaign');
        $campaignId = $campaign instanceof PopupCampaign ? $campaign->id : $campaign;

        $slugRule = 'unique:popup_campaigns,slug' . ($campaignId ? ',' . $campaignId : '');

        return [
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.ar' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $slugRule],
            'type' => ['required', 'in:' . implode(',', PopupCampaign::TYPES)],
            'status' => ['required', 'in:' . implode(',', PopupCampaign::STATUSES)],
            'priority' => ['required', 'integer', 'min:0'],
            'headline' => ['required', 'array'],
            'headline.en' => ['required', 'string', 'max:255'],
            'headline.ar' => ['required', 'string', 'max:255'],
            'subheadline' => ['nullable', 'array'],
            'subheadline.en' => ['nullable', 'string', 'max:255'],
            'subheadline.ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'button_text' => ['required', 'string', 'max:255'],
            'button_url' => ['nullable', 'url', 'max:2048'],
            'secondary_button_text' => ['nullable', 'string', 'max:255'],
            'media_type' => ['required', 'in:' . implode(',', PopupCampaign::MEDIA_TYPES)],
            'media_path' => [
                Rule::requiredIf(empty($campaignId)),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,mkv',
            ],
            'form_enabled' => ['sometimes', 'boolean'],
            'form_fields' => ['nullable', 'array'],
            'form_fields.*' => ['string', 'max:255'],
            'show_on_pages' => ['nullable', 'array'],
            'show_on_pages.*' => ['string', 'exists:pages,slug'],
            'audience_type' => ['required', 'in:' . implode(',', PopupCampaign::AUDIENCE_TYPES)],
            'trigger_type' => ['required', 'in:' . implode(',', PopupCampaign::TRIGGER_TYPES)],
            'trigger_value' => ['nullable', 'integer', 'min:0'],
            'show_every' => ['prohibited'],
            'max_impressions' => ['prohibited'],
            'product_ids' => ['sometimes', 'array'],
            'product_ids.*' => ['integer', Rule::exists('products', 'id')->where(fn ($q) => $q->whereNull('deleted_at'))],
            'shop_ids' => ['sometimes', 'array'],
            'shop_ids.*' => ['integer', Rule::exists('shops', 'id')->where(fn ($q) => $q->whereNull('deleted_at'))],
            'recipe_ids' => ['sometimes', 'array'],
            'recipe_ids.*' => ['integer', 'exists:recipes,id'],
            'basket_ids' => ['sometimes', 'array'],
            'basket_ids.*' => ['integer', 'exists:baskets,id'],
            'promotion_ids' => ['sometimes', 'array'],
            'promotion_ids.*' => ['integer', 'exists:promotions,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'form_fields' => $this->normalizeArray($this->input('form_fields')),
            'form_enabled' => (bool) $this->input('form_enabled'),
        ];

        if ($this->has('show_on_pages')) {
            $merge['show_on_pages'] = $this->normalizeShowOnPageSlugList($this->input('show_on_pages'));
        }

        $this->merge($merge);
    }

    /**
     * @return list<string>
     */
    protected function normalizeShowOnPageSlugList(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $out = [];
        foreach (Arr::wrap($value) as $item) {
            if (is_string($item) && trim($item) !== '') {
                $out[] = trim($item);
            }
        }

        return array_values(array_unique($out));
    }

    protected function normalizeArray($value): ?array
    {
        if (is_null($value)) {
            return null;
        }

        $normalized = [];

        foreach (Arr::wrap($value) as $item) {
            if (is_string($item)) {
                $trimmed = trim($item);
                if ($trimmed !== '') {
                    $normalized[] = $trimmed;
                }
            } elseif (is_array($item)) {
                $normalized[] = $item;
            }
        }

        return $normalized ?: null;
    }
}
