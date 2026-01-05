<?php

namespace App\Http\Resources\SectionItem;

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
            'order' => $this->order,
            'item' => $this->item->getSectionData(),
            'action' => [
                'type' => 'page',
                // 'page' => ['slug' => $this->item->getSlug()],
                'params' => ['id' => $this->item->id]
            ]
        ];
    }
}
