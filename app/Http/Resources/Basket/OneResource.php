<?php

namespace App\Http\Resources\Basket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
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
            'image' => $this->imageUrl,
            'category' => $this->whenLoaded('category', fn() => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'num_varieties'   => (int) $this->num_varieties,
            'offer_ends_at'   => $this->offer_ends_at?->format('Y-m-d'),
            'original_price'    => round($this->calculated_price, 2),
            'discount_value'    => $this->discount,
            'discount_type'     => $this->discount_type,
            'discount_amount'   => round($this->discount_amount, 2),
            'final_price'       => round($this->final_price, 2),
            'rating'    => number_format((float) $this->rating, 1),
            'num_sold'  => (int) $this->num_sold,
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id'            => $item->id,
                        'quantity'      => (int) $item->quantity,
                        'unit_price'    => round($item->price, 2),
                        'subtotal'      => $item->subtotal,
                        'is_required'   => $item->is_required,
                        'min_quantity'  => (int) $item->min_quantity,
                        'max_quantity'  => (int) $item->max_quantity,
                        'can_adjust'    => $item->canAdjustQuantity(),

                        'product' => $item->relationLoaded('product') && $item->product ? [
                            'id'   => $item->product->id,
                            'name' => $item->product->name,
                            'image' => $item->product->media->first()?->url
                        ] : null,

                        'variant' => $item->relationLoaded('variant') && $item->variant
                            ? $item->variant->attributes_values->pluck('name')->toArray()
                            : null,


                        'companies' => $item->relationLoaded('companies')
                            ? $item->companies->map(function ($company) use ($item) {
                                return [
                                    'id'=>  $company->relationLoaded('brand') && $company->brand
                                    ? $company->brand->id : null,
                                    'name' => $company->relationLoaded('brand') && $company->brand
                                        ? $company->brand->name
                                        : null,
                                    'is_default'        => $company->isDefault(),
                                    'has_custom_price'  => $company->hasCustomPrice(),
                                    'effective_price'   => $company->effective_price !== null
                                        ? round($company->effective_price, 2)
                                        : round($item->price, 2),
                                ];
                            })->values()
                            : [],
                    ];
                })->values();
            }),


        ];
    }
}
