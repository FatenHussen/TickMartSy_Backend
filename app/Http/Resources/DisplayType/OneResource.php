<?php

namespace App\Http\Resources\DisplayType;

use App\Http\Resources\Governorate\AllResource;
use App\Http\Resources\Governorate\OneResource as GovernorateOneResource;
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
            // 'manual_model' => $this->manual_model,
            'image_url'                => $this->image_url,
            // 'fields' => $this->fields,

            // 'created_at'            => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
