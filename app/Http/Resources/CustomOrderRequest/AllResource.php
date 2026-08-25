<?php

namespace App\Http\Resources\CustomOrderRequest;

use App\Http\Resources\Address\AllResource as AddressResource;
use App\Http\Resources\Order\OneResource as OrderOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status instanceof \BackedEnum ? $this->status->value : $this->status;

        return [
            'id' => $this->id,
            'status' => $status,
            'status_label' => $this->status?->labelAr(),
            'description' => $this->description,
            'images' => $this->imageUrls(),
            'expected_at' => $this->expected_at?->toDateTimeString(),
            'order_id' => $this->order_id,
            'created_at' => $this->created_at?->toDateTimeString(),
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ] : null),
            'address' => $this->whenLoaded('address', fn () => AddressResource::make($this->address)),
            'payment_method' => $this->whenLoaded('paymentMethod', fn () => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name,
                'code' => $this->paymentMethod->code,
            ] : null),
        ];
    }
}
