<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ExtraDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'key'   => $this->detail_key,
            'value' => $this->detail_value,
        ];
    }
}
