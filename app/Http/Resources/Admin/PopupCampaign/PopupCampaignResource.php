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
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'headline' => $this->headline,
            'subheadline' => $this->subheadline,
            'description' => $this->description,
            'button_text' => $this->button_text,
            'secondary_button_text' => $this->secondary_button_text,
            'cta_type' => $this->cta_type,
            'cta_value' => $this->cta_value,
            'media_type' => $this->media_type,
            'media_path' => $this->media_path,
            'form_enabled' => $this->form_enabled,
            'form_fields' => $this->form_fields,
            'show_on_pages' => $this->show_on_pages,
            'audience_type' => $this->audience_type,
            'trigger_type' => $this->trigger_type,
            'trigger_value' => $this->trigger_value,
            'show_every' => $this->show_every,
            'max_impressions' => $this->max_impressions,
            'created_at' => $this->created_at?->toIsoString(),
            'updated_at' => $this->updated_at?->toIsoString(),
        ];
    }
}
