<?php

namespace App\Http\Resources\CustomOrderRequest;

use App\Http\Resources\Address\AllResource as AddressResource;
use App\Http\Resources\Order\OneResource as OrderOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
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
            'admin_note' => $this->admin_note,
            'rejection_reason' => $this->rejection_reason,
            'order_id' => $this->order_id,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
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
                'icon' => $this->paymentMethod->image_url ?? null,
            ] : null),
            'order' => $this->when(
                $this->relationLoaded('order') && $this->order,
                fn () => OrderOneResource::make($this->order->loadMissing('items'))
            ),
            'actions' => [
                'can_approve' => $status === 'waiting_approval',
                'can_cancel' => in_array($status, ['pending_pricing', 'waiting_approval'], true),
            ],
        ];
    }
}
