<?php

namespace App\Http\Resources\User;

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
            'content' => [
                'headline' => $this->headline,
                'subheadline' => $this->subheadline,
                'description' => $this->description,
            ],
            'buttons' => [
                'primary' => $this->button_text,
                'secondary' => $this->secondary_button_text,
            ],
            'cta' => [
                'type' => $this->cta_type,
                'value' => $this->cta_value,
            ],
            'media' => [
                'type' => $this->media_type,
                'path' => asset($this->media_path),
            ],
            'form' => [
                'enabled' => $this->form_enabled,
                'fields' => $this->form_fields,
            ],
            'display' => [
                'pages' => $this->show_on_pages,
                'audience_type' => $this->audience_type,
            ],
            'trigger' => [
                'type' => $this->trigger_type,
                'value' => $this->trigger_value,
            ],
            'frequency' => [
                'show_every' => $this->show_every,
                'max_impressions' => $this->max_impressions,
            ],
        ];
    }
}
