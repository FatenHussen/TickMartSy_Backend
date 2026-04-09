<?php

namespace App\Http\Resources\Admin\VendorWithdrawRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'vendor' => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
                'owner_name' => $this->vendor?->owner_name,
                'owner_phone' => $this->vendor?->owner_phone,
            ],
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'transfer_reference' => $this->transfer_reference,
            'note' => $this->note,
            'rejection_reason' => $this->rejection_reason,
            'requested_at' => $this->requested_at?->format('Y-m-d H:i:s'),
            'processed_at' => $this->processed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
