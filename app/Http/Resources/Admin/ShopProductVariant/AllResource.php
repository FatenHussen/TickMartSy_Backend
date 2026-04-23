<?php

namespace App\Http\Resources\Admin\ShopProductVariant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        // Check if productVariant exists
        if (!$this->productVariant) {
            return [
                'id' => $this->id,
                'label' => 'متغير غير متوفر',
            ];
        }

        // Get product name
        $productNameData = $this->productVariant->product->name ?? "product";
        $productName = is_array($productNameData)
            ? ($productNameData[$locale] ?? $productNameData['ar'] ?? $productNameData['en'] ?? '')
            : (string) $productNameData;

        // Get attributes as string
        $attributes = $this->productVariant->getAttributesWithDetails();
        $attributesParts = [];

        foreach ($attributes as $attr) {
            $attrNameData = $attr['category_attribute']['name'] ?? [];
            $attrName = is_array($attrNameData)
                ? ($attrNameData[$locale] ?? $attrNameData['ar'] ?? $attrNameData['en'] ?? '')
                : (string) $attrNameData;

            $attrValueData = $attr['name'] ?? [];
            $attrValue = is_array($attrValueData)
                ? ($attrValueData[$locale] ?? $attrValueData['ar'] ?? $attrValueData['en'] ?? '')
                : (string) $attrValueData;

            if (!empty($attrName) && !empty($attrValue)) {
                $attributesParts[] = "{$attrName}: {$attrValue}";
            }
        }

        $attributesString = implode(' | ', $attributesParts);

        // Get shop name
        $shopNameData = $this->shop->name ?? [];
        $shopName = is_array($shopNameData)
            ? ($shopNameData[$locale] ?? $shopNameData['ar'] ?? $shopNameData['en'] ?? 'متجر غير معروف')
            : (string) $shopNameData;

        // Build label
        $label = $productName;
        if (!empty($attributesString)) {
            $label .= " ({$attributesString})";
        }
        $label .= " - {$shopName}";

        return [
            'id' => $this->id,
            'label' => $label,
            'product_number' => $this->productVariant?->product?->product_number,
            'variant_image' => $this->resolveVariantImage(),
            'shop_id' => $this->shop_id,
            'price' => $this->price,
            'discount' => $this->discount,
            'price_after_discount' => $this->price_after_discount,
            'is_restaurant' => (bool) ($this->shop?->is_restaurant ?? false),
            'city_id' => $this->shop?->city_id ?? $this->shop?->area?->city_id,
        ];
    }

    private function resolveVariantImage(): ?string
    {
        $variantImage = $this->productVariant?->media
            ?->where('collection', 'variant')
            ?->sortBy('order')
            ?->first();

        if ($variantImage) {
            return $variantImage->url;
        }

        $productImage = $this->productVariant?->product?->media
            ?->sortBy('order')
            ?->first();

        return $productImage?->url;
    }
}
