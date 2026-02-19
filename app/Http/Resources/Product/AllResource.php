<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Badge\OneResource;
use App\Models\Badge;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'category'              => $this->category->name,
            'name'                  => $this->name,
            'description'           => $this->description,
            'country'               => $this->country,
            'price'                 => $this->price,
            'price_after_discount'  => $this->price_after_discount,
            'amount_saved'          => $this->price -  $this->price_after_discount,
            'quantity'              => $this->quantity,
            'image'                 => $this->media->first()?->url,
            'discount'              => '',
            'created_at'            => $this->created_at,
            'sold_number'           => $this->sold_quantity ?? 0,
            'rating' => $this->average_rating ?? 0,
            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'bottom')->values()
            ),

        ];
    }
}
