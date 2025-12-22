<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $response = [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                $this->phone ? 'phone' : 'email' => $this->phone ?? $this->email,
            ],
        ];

        $response['token'] = $this->createToken('AUTH')->plainTextToken;

        return $response;
    }
}
