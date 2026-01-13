<?php

namespace App\Http\Resources\Recipe;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeStepResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'step_number' => $this->step_number,
            'instruction' => $this->instruction,
            'time_minutes' => $this->time_minutes,
            'heat_level' => $this->heat_level,
        ];
    }
}
