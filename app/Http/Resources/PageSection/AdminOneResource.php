<?php

namespace App\Http\Resources\PageSection;

use App\Enums\VariantSection;
use App\Http\Resources\Section\SectionApiItemResource;
use App\Http\Resources\SectionItem\OneResource as SectionItemOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOneResource extends JsonResource
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
            'name' => $this->getTranslations('name') ?? null,
            'section_id' => $this->section->id,
            'section_name' => $this->section->name,
            'content_type' => $this->section->contentType(),
            'page_id' => $this->page->id,
            'page_name' => $this->page->title,
            'position' => $this->position,
            'order' => $this->order,
            'display_type_id' => $this->display_type_id,
            'variant' => $this->variant ?? VariantSection::Horizontal->value,
            'background_color' => $this->background_color,
            'background_card_color' => $this->background_card_color,
            'filters' => $this->filters,
            'show_when' => $this->show_when,
        ];
    }
}
