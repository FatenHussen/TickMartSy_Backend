<?php

namespace App\Http\Resources\PageSection;

use App\Enums\VariantSection;
use App\Http\Resources\SectionItem\OneResource as SectionItemOneResource;
use App\Http\Resources\Section\SectionApiItemResource;
use App\Models\FlashSale;
use App\Models\SectionItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
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
            'variant' => $this->variant ?? VariantSection::Horizontal->value,
            'background_color' => $this->background_color,
            'background_card_color' => $this->background_card_color,
            ...$this->resolveFlashSaleFields(),
            'see_more' => $this->section->see_more
                ? [
                    'page_slug' => $this->section->see_more_slug,
                    'params' => $this->filters
                ]
                : null,
            'show_when' => $this->show_when,

            'action' => [
                'page_slug' => $this->section->details_slug,

            ],
            // 'items' => $this->section->type === 'api'
            //     ? SectionApiItemResource::collection($this->section->apiData($this->filters))
            //     : SectionItemOneResource::collection($this->section->sectionItems),
            'items' => $this->section->type === 'api'
                ? SectionApiItemResource::collection(
                    $this->section->apiData($this->filters) ?? collect()
                )
                : SectionItemOneResource::collection(
                    $this->visibleSectionItems()
                ),

        ];
    }

    private function visibleSectionItems()
    {
        return collect($this->section->sectionItems ?? collect())
            ->filter(fn(SectionItem $sectionItem) => $this->isSectionItemActive($sectionItem));
    }

    private function isSectionItemActive(SectionItem $sectionItem): bool
    {
        $item = $sectionItem->item;

        if (!$item) {
            return false;
        }

        if (array_key_exists('is_active', $item->getAttributes())) {
            if (!(bool) $item->is_active) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{end_date: mixed, discount: mixed, discount_type: mixed}
     */
    private function resolveFlashSaleFields(): array
    {
        if (($this->filters['type'] ?? null) !== 'latest_flash_sale') {
            return [
                'end_date' => null,
                'discount' => null,
                'discount_type' => null,
            ];
        }

        static $cached = null;
        static $loaded = false;

        if (!$loaded) {
            $flashSale = FlashSale::query()
                ->active()
                ->latest('id')
                ->first(['end_date', 'discount', 'discount_type']);

            $cached = $flashSale
                ? [
                    'end_date' => $flashSale->end_date,
                    'discount' => $flashSale->discount,
                    'discount_type' => $flashSale->discount_type,
                ]
                : [
                    'end_date' => null,
                    'discount' => null,
                    'discount_type' => null,
                ];
            $loaded = true;
        }

        return $cached;
    }
}
