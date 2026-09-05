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
            'country' => $this->country?->name,
            ...$this->withCurrency($this->price, 'price'),
            ...$this->withCurrency($this->cost_price, 'cost_price'),
            ...$this->withCurrency($this->price_after_discount, 'price_after_discount'),
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'warranty_period' => $this->warranty_period,
            'warranty' => $this->warranty ? [
                'id' => $this->warranty->id,
                'name' => $this->warranty->name,
                'description' => $this->warranty->description,
            ] : null,
            'stock' => $this->stock,
            'max_purchase_quantity' => $this->max_purchase_quantity,
            'is_visible' => $this->is_visible,
            'discount_type' => $this->discount_type,
            'is_restaurant' => (bool) ($this->is_restaurant ?? $this->category?->is_restaurant ?? false),

            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'time_prepare' => optional($this->time_prepare)->format('H:i'),
            'delivery_time' => $this->effective_delivery_time,
            'bought_with' => AllResource::collection($this->bought_with_products_list ?? []),
            'is_instant_delivery' => $this->is_instant_delivery,
            'rating' => $this->average_rating ?? 0,
            'rating_breakdown' => $this->getRatingBreakdown() ?? [],

            'thumbnail' => $this->thumbnail_url,

            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'is_restaurant' => (bool) ($this->category?->is_restaurant ?? false),
            ],
            'attributes_map' => new AttributeMapResource($this->variants),

            'shop_variants' => $this->shopVariantsPayload($request),



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
                $this->badges->where('position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'bottom')->values()
            ),

            'icons' => \App\Http\Resources\Icon\IconSimpleResource::collection(
                $this->whenLoaded('icons', $this->icons ?? collect())
            ),

        ];
    }

    private function shopVariantsPayload($request)
    {
        $variants = ($this->variants ?? collect())
            ->map(fn ($variant) => (new ShopVariantResource($variant))->resolve($request))
            ->filter()
            ->values();

        if ($variants->isNotEmpty()) {
            return $variants;
        }

        return collect([$this->fallbackShopVariant()]);
    }

    private function fallbackShopVariant(): array
    {
        return [
            'id' => null,
            'variant_id' => null,
            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'attributes' => [],
            ...$this->withCurrency($this->price, 'price'),
            ...$this->withCurrency($this->price - $this->price_after_discount, 'discount'),
            ...$this->withCurrency($this->price_after_discount, 'price_after_discount'),
            'quantity' => $this->quantity,
            'shop_id' => null,
            'is_restaurant' => (bool) ($this->is_restaurant ?? $this->category?->is_restaurant ?? false),
            'city_id' => null,
            'images' => MediaResource::collection($this->media ?? collect()),
        ];
    }
}
