<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Governorate\OneResource as GovernorateOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
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

            'name' => $this->name,
            'description' => $this->description,

            'image' => $this->image,
            'video_url' => $this->video_url,

            'rating' => $this->rating,
            'orders_count' => $this->orders_count,

            'discount' => $this->discount,

            'badges' => [
                'top' => $this->whenLoaded('topBadge', function () {
                    return [
                        'id' => $this->topBadge->id,
                        'name' => $this->topBadge->name,
                        'color' => $this->topBadge->color,
                        'icon' => $this->topBadge->icon,
                    ];
                }),
                'bottom' => $this->whenLoaded('bottomBadge', function () {
                    return [
                        'id' => $this->bottomBadge->id,
                        'name' => $this->bottomBadge->name,
                        'color' => $this->bottomBadge->color,
                        'icon' => $this->bottomBadge->icon,
                    ];
                }),
            ],

            'created_at' => $this->created_at,
        ];
    }
}
