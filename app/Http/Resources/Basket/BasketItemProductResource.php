<?php

namespace App\Http\Resources\Basket;

use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemProductResource extends JsonResource
{
    public function toArray($request)
    {
        $media = $this->media ?? collect();

        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'image' => $media->first()?->url ?? null,
            'is_instant_delivery' => $this->is_instant_delivery
        ];
    }
}
