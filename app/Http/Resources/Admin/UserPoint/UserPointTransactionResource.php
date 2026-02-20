<?php

namespace App\Http\Resources\Admin\UserPoint;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPointTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'points' => $this->points,
            'source' => $this->source,
            'status' => $this->status,
            'reason' => $this->reason,
            'rule' => $this->rule ? [
                'id' => $this->rule->id,
                'code' => $this->rule->code,
                'title' => $this->rule->title,
            ] : null,
            'admin' => $this->admin ? [
                'id' => $this->admin->id,
                'name' => $this->admin->name,
            ] : null,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
