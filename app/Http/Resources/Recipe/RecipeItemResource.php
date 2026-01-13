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

        $alternatives = [
            'same_shop' => [],
            'other_shops' => [],
        ];

        if ($this->switchable_category_level) {

            $parentCategory = Category::find($this->switchable_category_level);

            if ($parentCategory) {

                $categoryIds = $parentCategory
                    ->leafDescendants()
                    ->pluck('id')
                    ->toArray();

                // إذا بدك تسمح بالمنتج من نفس الفئة لو كانت Leaf
                if ($parentCategory->children()->count() === 0) {
                    $categoryIds[] = $parentCategory->id;
                }
                Log::info("Hi");
                Log::info($parentCategory);

                Log::info($categoryIds);

                // 3️⃣ كل البدائل
                $variants = ShopProductVariant::with([
                    'productVariant.product',
                    'shop'
                ])
                    ->whereHas('productVariant.product', function ($q) use ($categoryIds) {
                        $q->whereIn('category_id', $categoryIds);
                    })
                    ->where('quantity', '>', 0)
                    ->get();

                // 4️⃣ تقسيمهم
                $alternatives['same_shop'] = ShopProductVariantResource::collection(
                    $variants->where('shop_id', $itemShopId)->values()
                );

                $alternatives['other_shops'] = ShopProductVariantResource::collection(
                    $variants->where('shop_id', '!=', $itemShopId)->values()
                );
            }
        }

        return [
            'product_id' => $this->shopProductVariant->productVariant->product->id,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'name' => $this->shopProductVariant->productVariant->product->name,
            'is_required' => $this->is_required,
            'default_quantity' => $this->quantity,
            'min_quantity' => $this->min_quantity ?? $this->quantity,
            'max_quantity' => $this->max_quantity ?? $this->quantity,
            'price' => $this->shopProductVariant->price,
            'alternatives' => $alternatives,
        ];
    }
}
