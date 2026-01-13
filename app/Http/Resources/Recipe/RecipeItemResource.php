<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Governorate\AllResource;
use App\Http\Resources\Governorate\OneResource as GovernorateOneResource;
use App\Models\Category;
use App\Models\ShopProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class RecipeItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $itemShopId = $this->shopProductVariant->shop_id;

        $same_shop = [];
        $other_shops = [];

        if ($this->switchable_category_level) {

            $parentCategory = Category::find($this->switchable_category_level);

            if ($parentCategory) {

                $categoryIds = $parentCategory
                    ->leafDescendants()
                    ->pluck('id')
                    ->toArray();

                if ($parentCategory->children()->count() === 0) {
                    $categoryIds[] = $parentCategory->id;
                }

                $variants = ShopProductVariant::with([
                    'productVariant.product',
                    'shop'
                ])
                    ->whereHas('productVariant.product', function ($q) use ($categoryIds) {
                        $q->whereIn('category_id', $categoryIds);
                    })
                    ->where('quantity', '>', 0)
                    ->where('id', '!=', $this->shop_product_variant_id)
                    ->get();

                $same_shop = ShopProductVariantResource::collection(
                    $variants->where('shop_id', $itemShopId)->values()
                );

                $other_shops = ShopProductVariantResource::collection(
                    $variants->where('shop_id', '!=', $itemShopId)->values()
                );
            }
        }

        return [
            'terms' => [
                'is_required' => $this->is_required,
                'default_quantity' => $this->quantity,
                'min_quantity' => $this->min_quantity ?? $this->quantity,
                'max_quantity' => $this->max_quantity ?? $this->quantity,
            ],
            'main_item' => [
                'product_id' => $this->shopProductVariant->productVariant->product->id,
                'shop_product_variant_id' => $this->shop_product_variant_id,
                'image_url' => $this->shopProductVariant->productVariant->product->image_url,
                'name' => $this->shopProductVariant->productVariant->product->name,
                'price' => $this->shopProductVariant->price,
            ],
            'alternatives' => $same_shop,
            'other_shops' => $other_shops
        ];
    }
}
