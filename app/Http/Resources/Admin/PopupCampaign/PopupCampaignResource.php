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
        ];
    }
}
