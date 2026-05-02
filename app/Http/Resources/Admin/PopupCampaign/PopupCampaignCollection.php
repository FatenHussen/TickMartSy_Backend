<?php

namespace App\Http\Resources\Admin\PopupCampaign;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopupCampaignCollection extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'type' => $this->type,
            'priority' => $this->priority,
            'headline' => $this->headline,
            'audience_type' => $this->audience_type,
            'show_on_pages' => $this->relationLoaded('pages')
                ? $this->pages->pluck('slug')->values()->all()
                : [],
            'created_at' => $this->created_at?->toIsoString(),
        ];
    }
}
