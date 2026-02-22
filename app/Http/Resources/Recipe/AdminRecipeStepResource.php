<?php

namespace App\Http\Resources\Recipe;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminRecipeStepResource extends JsonResource
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
            'instruction' => $this->getTranslations('instruction'),
            'time_minutes' => $this->getTranslations('time_minutes'),
            'heat_level' => $this->getTranslations('heat_level'),
        ];
    }
}
