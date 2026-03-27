<?php

namespace App\Http\Resources\Admin\SaleCountry;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon_url,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
