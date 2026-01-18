<?php

namespace App\Http\Resources\Basket;

use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemCompanyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' =>  $this->brand->id,
            'name' =>$this->brand->name,
            'is_default' => $this->isDefault(),
            'has_custom_price' => $this->hasCustomPrice(),
            'effective_price' => 
            $this->effective_price !== null
                ?
                 round($this->effective_price, 2)
                : round($this->basketItem->price, 2)
                ,
        ];
    }
}
