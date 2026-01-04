<?php

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'category_id'           => $this->category->name,

            'name'                  => $this->name,
            'description'           => $this->description,
            'full_description'      => $this->full_description,
            'country'               => $this->country,

            'sku'                   => $this->sku,
            'model'                 => $this->model,
            'price'                 => $this->price,
            'price_after_discount'  => $this->price_after_discount,
            'quantity'              => $this->quantity,
            'barcode'               => $this->barcode,
            'time_prepare'          => $this->time_prepare,
            'bought_with'           => $this->bought_with,
            'is_instant_delivery'   => $this->is_instant_delivery,

            'image'                 => $this->media->first()?->url,
            'images'                => $this->media->pluck('url'),

            'created_at'            => $this->created_at,
        ];
    }
}
