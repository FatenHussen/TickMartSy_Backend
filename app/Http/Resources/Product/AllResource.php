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

        // Get first shop_product_variant_id for this product
        $shopProductVariantId = null;
        $firstVariant = $this->variants()->first();
        if ($firstVariant) {
            $firstShopVariant = $firstVariant->shopVariants()->first();
            if ($firstShopVariant) {
                $shopProductVariantId = $firstShopVariant->id;
            }
        }

        return [
            'id'                    => $this->id,
            'name'                  => $this->name,

            'category'              => $this->category->name,
            'description'           => $this->description,
            'country'               => $this->country,
            ...$this->withCurrency($this->price, 'price'),
            ...$this->withCurrency($this->price_after_discount, 'price_after_discount'),
            ...$this->withCurrency($this->price - $this->price_after_discount, 'amount_saved'),
            'quantity'              => $this->quantity,
            'image'                 => $this->media->first()?->url,
            'discount'              => '',
            'created_at'            => $this->created_at,
            'sold_number'           => $this->sold_quantity ?? 0,
            'rating' => $this->average_rating ?? 0,
            'is_favorite' => (bool) ($this->is_favorite ?? false),
            'shop_product_variant_id' => $shopProductVariantId,

            'vendor' => $this->vendor->name,
            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'bottom')->values()
            ),

        ];
    }
}
