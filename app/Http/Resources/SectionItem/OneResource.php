<?php

namespace App\Http\Resources\SectionItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Banner;

class OneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $item = $this->item;

        return [
            'id' => $this->id,
            'link' => $item && $this->item_type === Banner::class ? $item->link : $this->link,
            'order' => $this->order,
            'item' => $item ? $item->toSectionArray() : null,
        ];
    }
}
