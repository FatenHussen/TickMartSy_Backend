<?php

namespace App\Http\Resources\Admin\Category\CategoryDetail;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'name'     => $this->getTranslations('name'),
            'category' => [
                'id'   => $this->category->id,
                'name' => $this->category->getTranslations('name'),
            ],
            'is_active' => $this->is_active,

            'created_at' => $this->created_at,
        ];
    }
}
