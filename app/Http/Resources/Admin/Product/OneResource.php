<?php


namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'full_description' => $this->getTranslations('full_description'),

            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'price_after_discount' => $this->price_after_discount,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'warranty_period' => $this->warranty_period,
            'is_visible' => $this->is_visible,
            'is_active' => (bool) $this->is_active,

            'sku' => $this->sku,
            'model' => $this->model,
            'barcode' => $this->barcode,
            'time_prepare' => optional($this->time_prepare)->format('H:i'),
            'bought_with'           => $this->bought_with_products_list->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            }),
            'is_instant_delivery' => $this->is_instant_delivery,

            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,

            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],

            'brand' => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
            ] : null,

            'origin_country' => $this->originCountry ? [
                'id' => $this->originCountry->id,
                'name' => $this->originCountry->name,
            ] : null,

            'sale_country' => $this->saleCountry ? [
                'id' => $this->saleCountry->id,
                'name' => $this->saleCountry->name,
                'icon' => $this->saleCountry->icon_url,
            ] : null,

            'vendor' => $this->vendor ? [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
            ] : null,

            'approval_status' => $this->approval_status?->value,
            'approval_status_label' => match ($this->approval_status?->value) {
                'pending' => 'قيد الانتظار',
                'approved' => 'مقبول',
                'rejected' => 'مرفوض',
                default => null,
            },
            'rejection_reason' => $this->rejection_reason,

            'variants' => ($this->variants ?? collect())->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'attributes' => collect($variant->attributesValues)->map(function ($value) {
                        return [
                            'attribute' => $value->categoryAttribute?->name,
                            'value' => $value->name,
                            'type' => $value->categoryAttribute->type,
                        ];
                    }),

                    'shops' => ($variant->shopVariants ?? collect())->map(function ($sv) {
                        return [
                            'id' => $sv->id,
                            'shop_id' => $sv->shop_id,
                            'shop_name' => $sv->shop?->name,
                            'price' => $sv->price,
                            'quantity' => $sv->quantity,
                        ];
                    })->values(),

                    'images' => ($variant->media ?? collect())->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'url' => $img->url,
                        ];
                    }),
                ];
            })->values(),

            // Category Details
            'category_details' => ($this->categoryDetails ?? collect())->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'name' => $detail->categoryDetail?->name,
                    'value' => $detail->getTranslations('detail_value'),
                ];
            })->values(),

            // Extra Details
            'extra_details' => ($this->extraDetails ?? collect())->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'key' => $detail->getTranslations('detail_key') ?? [],
                    'value' => $detail->getTranslations('detail_value') ?? [],
                    'price' => (float) $detail->price,
                ];
            })->values(),

            // Product Images
            'images' => ($this->media ?? collect())->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => $img->url,
                ];
            })->values(),

            // SEO Fields
            'seo_title' => $this->getTranslations('seo_title'),
            'seo_description' => $this->getTranslations('seo_description'),
            'seo_keywords' => $this->getTranslations('seo_keywords'),
            'seo_image' => $this->seo_image ? asset('storage/' . $this->seo_image) : null,

            // Badges
            'badges' => ($this->badges ?? collect())->map(function ($badge) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'icon' => $badge->icon ? asset('storage/' . $badge->icon) : null,
                    'position' => $badge->pivot?->position,
                ];
            })->values(),

            // Icons
            'icons' => ($this->icons ?? collect())->map(function ($icon) {
                return [
                    'id' => $icon->id,
                    'name' => $icon->name,
                    'icon' => $icon->icon ? asset('storage/' . $icon->icon) : null,
                ];
            })->values(),

            // Rating
            'rating' => round((float) $this->average_rating, 1),
            'rating_count' => $this->ratings()->count(),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
