<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $response = [
            'driver' => [
                'id' => $this->id,
                'phone' => $this->phone,
                'image' => $this->image_url
            ],
        ];

        $response['token'] = $this->createToken('AUTH')->plainTextToken;

        return $response;
    }
}
