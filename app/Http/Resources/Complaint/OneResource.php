<?php

namespace App\Http\Resources\Complaint;

use App\Http\Resources\EndUser\AllResource as EndUserAllResource;
use App\Http\Resources\Order\AllResource;
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
            'id'           => $this->id,
            'order_id'     => $this->order_id,
            'message'      => $this->message,
            'status'       => $this->status->value,
            'type'       => $this->type->value,
            'admin_response' => $this->admin_response,
            'images'       => $this->images,
            'user' => EndUserAllResource::make($this->user),
            'order' => AllResource::make($this->order),
            'created_at'   => $this->created_at?->toDateTimeString(),
        ];
    }
}
