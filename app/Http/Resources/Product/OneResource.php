<?php


namespace App\Http\Resources\Product;

use App\Models\Product;
use App\Services\Base\LocationService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'full_description' => $this->full_description,
            'country' => $this->country,
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'quantity' => $this->quantity,

            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'time_prepare' => optional($this->time_prepare)->format('H:i'),
            'bought_with'=>  AllResource::collection($this->boughtWithProduct()),
            'is_instant_delivery' => $this->is_instant_delivery,
            'rating' => $this->average_rating,

            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'attributes_map' => new AttributeMapResource($this->variants),

            'shop_variants' => $this->variants
                ->map(fn($variant) => new ShopVariantResource($variant))
                ->filter()
                ->values(),



            'category_details' => CategoryDetailResource::collection(
                $this->categoryDetails
            ),

            'extra_details' => ExtraDetailResource::collection(
                $this->extraDetails
            ),

            'images' => MediaResource::collection(
                $this->media
            ),
            
        ];
    }

}
