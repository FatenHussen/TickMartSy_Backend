<?php

namespace App\Http\Resources\Admin\Store;

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
        return[
            'id'            => $this->id,
            'name'          => $this->name,
            'store_name'    => $this->store_name,
            'email'         => $this->email,
            'owner_phone'   => $this->owner_phone,
            'status'        => $this->status,
            'rating'        => $this->rating,
            'area_id'       => $this->area_id,
            'category_id'   => $this->category_id,
            'created_at'    => $this->created_at,
        ];
    }
}
