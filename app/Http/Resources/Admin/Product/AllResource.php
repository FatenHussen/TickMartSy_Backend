<?php

namespace App\Http\Resources\Admin\Product;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'category_id'           => $this->category->name,
            'is_restaurant_category' => (bool) ($this->category?->is_restaurant ?? false),
            'brand_id'              => $this->brand?->name,

            'name'                  => $this->name,
            'description'           => $this->description,
            'full_description'      => $this->full_description,

            'sku'                   => $this->sku,
            'model'                 => $this->model,
            ...$this->withCurrency($this->price, 'price'),
            ...$this->withCurrency($this->cost_price, 'cost_price'),
            ...$this->withCurrency($this->price_after_discount, 'price_after_discount'),
            'discount'              => $this->discount,
            'discount_type'         => $this->discount_type,
            'quantity'              => $this->quantity,
            'unit'                  => $this->unit,
            'unit_option'           => $this->unitOption ? [
                'id' => $this->unitOption->id,
                'name' => $this->unitOption->name,
            ] : null,
            'warranty_period'       => $this->warranty_period,
            'expiry_date'           => $this->expiry_date?->format('Y-m-d'),
            'is_visible'            => $this->is_visible,
            'is_active'             => (bool) $this->is_active,
            'barcode'               => $this->barcode,
            'time_prepare'          => $this->time_prepare,
            'bought_with'           => $this->bought_with_products_list->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            }),
            'is_instant_delivery'   => $this->is_instant_delivery,

            'thumbnail'             => $this->thumbnail_url,

            'vendor' => $this->vendor ? [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
            ] : null,

            'origin_country' => $this->originCountry?->name,
            'sale_country' => $this->saleCountry?->name,

            'approval_status' => $this->approval_status?->value,
            'approval_status_label' => match ($this->approval_status?->value) {
                'pending' => 'قيد الانتظار',
                'approved' => 'مقبول',
                'rejected' => 'مرفوض',
                default => null,
            },

            'image'                 => $this->media->first()?->url,
            'images'                => $this->media->pluck('url'),

            'created_at'            => $this->created_at,
        ];
    }
}
