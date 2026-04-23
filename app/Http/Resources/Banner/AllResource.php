<?php

namespace App\Http\Resources\Banner;

use App\Http\Resources\Area\OneResource as AreaOneResource;
use App\Http\Resources\Vendor\AllResource as VendorAllResource;
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
            'id'                    => $this->id,
            'title'                  => $this->title,
            'description'            => $this->description,
            'button_text'            => $this->button_text,
            'image_url'                => $this->image_url,
            'link' =>                  $this->link,
            'expires_at' =>           $this->expires_at?->format('Y-m-d H:i'),
            // 'is_active'             => $this->is_active,
            // 'order'             => $this->order,
            'is_active'             => $this->is_active,
            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
