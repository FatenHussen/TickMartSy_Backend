<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Badge\OneResource;
use App\Models\Badge;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

class AllResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        // Prefer an active shop link; skip variants that have none.
        $shopProductVariantId = $this->resolveShopProductVariantId();

        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'is_restaurant'         => (bool) ($this->is_restaurant ?? $this->category?->is_restaurant ?? false),

            'category'              => $this->category?->name,
            'description'           => $this->description,
            'country'               => $this->country?->name,
            ...$this->withCurrency($this->price, 'price'),
            ...$this->withCurrency($this->final_price, 'price_after_discount'),
            ...$this->withCurrency($this->price - $this->final_price, 'amount_saved'),
            'quantity'              => $this->quantity,
            'image'                 => $this->image_url,
            'thumbnail'             => $this->thumbnail_url,
            'discount'              => '',
            'created_at'            => $this->created_at,
            'sold_number'           => $this->sold_quantity ?? 0,
            'rating' => $this->average_rating ?? 0,
            'is_favorite' => (bool) ($this->is_favorite ?? false),
            'shop_product_variant_id' => $shopProductVariantId,

            'vendor' => $this->vendor?->name,
            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'bottom')->values()
            ),

        ];
    }

    private function resolveShopProductVariantId(): ?int
    {
        $variants = $this->relationLoaded('variants')
            ? $this->variants
            : $this->variants()->with('shopVariants')->get();

        foreach ($variants as $variant) {
            if ($variant->is_active === false) {
                continue;
            }

            $shopVariants = $variant->relationLoaded('shopVariants')
                ? $variant->shopVariants
                : $variant->shopVariants()->get();

            $shopVariant = $shopVariants->first();

            if ($shopVariant) {
                return (int) $shopVariant->id;
            }
        }

        return null;
    }
}
