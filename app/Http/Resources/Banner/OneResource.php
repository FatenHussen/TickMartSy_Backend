<?php

namespace App\Http\Resources\Banner;

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
            'id'                    => $this->id,
            'title'                  => $this->getTranslations('title'),
            'description'            => $this->getTranslations('description'),
            'image_url'                => $this->image_url,
            'link' =>                  $this->link,
            // 'is_active'             => $this->is_active,
            // 'order'             => $this->order,
            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
