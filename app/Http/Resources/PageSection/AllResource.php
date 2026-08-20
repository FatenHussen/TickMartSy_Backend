<?php

namespace App\Http\Resources\PageSection;

use App\Enums\SectionLayout;
use App\Enums\VariantSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? $this->section->name,
            'type' => $this->section->type,
            'position' => $this->position,
            'order' => $this->order,
            'display_type_id' => $this->display_type_id,
            'layout' => $this->layout ?? SectionLayout::Slider->value,
            'variant' => $this->variant ?? VariantSection::Horizontal->value,
            'background_color' => $this->background_color,
            'background_card_color' => $this->background_card_color,
            'show_when' => $this->show_when,

        ];
    }
}
