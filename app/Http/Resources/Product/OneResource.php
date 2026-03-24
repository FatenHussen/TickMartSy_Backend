<?php


namespace App\Http\Resources\Product;

use App\Models\Product;
use App\Services\Base\LocationService;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

class OneResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'full_description' => $this->full_description,
            'country' => $this->country,
            ...$this->withCurrency($this->price, 'price'),
            ...$this->withCurrency($this->cost_price, 'cost_price'),
            ...$this->withCurrency($this->price_after_discount, 'price_after_discount'),
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'warranty_period' => $this->warranty_period,
            'is_visible' => $this->is_visible,
            'discount_type' => $this->discount_type,

            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'time_prepare' => optional($this->time_prepare)->format('H:i'),
            'bought_with' => OneResource::collection($this->bought_with_products_list ?? []),
            'is_instant_delivery' => $this->is_instant_delivery,
            'rating' => $this->average_rating ?? 0,
            'rating_breakdown' => $this->getRatingBreakdown() ?? [],

            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,

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

            'available_shops' => $this->getAvailableShops()->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->name,
                ];
            }),
            'is_favorite' => (bool) ($this->is_favorite ?? false),


            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'bottom')->values()
            ),

            'icons' => \App\Http\Resources\Icon\IconSimpleResource::collection(
                $this->whenLoaded('icons', $this->icons ?? collect())
            ),

        ];
    }
}
