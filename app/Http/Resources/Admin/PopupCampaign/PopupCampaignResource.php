<?php

namespace App\Http\Resources\Admin\PopupCampaign;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopupCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' =>  $this->getTranslations('title'),
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'headline' => $this->getTranslations('headline'),
            'subheadline' => $this->getTranslations('subheadline'),
            'description' => $this->getTranslations('description'),
            'button_text' => $this->button_text,
            'button_url' => $this->button_url,
            'secondary_button_text' => $this->secondary_button_text,
            'media_type' => $this->media_type,
            'media_path' => $this->media_path,
            'form_enabled' => $this->form_enabled,
            'form_fields' => $this->form_fields,
            'show_on_pages' => $this->relationLoaded('pages')
                ? $this->pages->pluck('slug')->values()->all()
                : [],
            'audience_type' => $this->audience_type,
            'trigger_type' => $this->trigger_type,
            'trigger_value' => $this->trigger_value,
            'show_every' => $this->show_every,
            'max_impressions' => $this->max_impressions,
            'product_ids' => $this->whenLoaded('products', fn () => $this->products->pluck('id')->values()->all()),
            'shop_ids' => $this->whenLoaded('shops', fn () => $this->shops->pluck('id')->values()->all()),
            'recipe_ids' => $this->whenLoaded('recipes', fn () => $this->recipes->pluck('id')->values()->all()),
            'basket_ids' => $this->whenLoaded('baskets', fn () => $this->baskets->pluck('id')->values()->all()),
            'promotion_ids' => $this->whenLoaded('promotions', fn () => $this->promotions->pluck('id')->values()->all()),
            'created_at' => $this->created_at?->toIsoString(),
            'updated_at' => $this->updated_at?->toIsoString(),
        ];
    }
}
