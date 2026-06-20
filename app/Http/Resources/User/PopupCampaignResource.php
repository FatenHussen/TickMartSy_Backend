<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Admin\ShopVendorService\AllResource as ShopVendorServiceAllResource;
use App\Http\Resources\Basket\AllResource as BasketAllResource;
use App\Http\Resources\Product\AllResource as ProductAllResource;
use App\Http\Resources\Promotion\AllResource as PromotionAllResource;
use App\Http\Resources\Recipe\AllResource as RecipeAllResource;
use App\Http\Resources\Shop\AllResource as ShopAllResource;
use Illuminate\Http\Request;
use App\Http\Resources\Promotion\UserOneResource as PromotionUserOneResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PopupCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'content' => [
                'headline' => $this->headline,
                'subheadline' => $this->subheadline,
                'description' => $this->description,
            ],
            'buttons' => [
                'primary' => $this->button_text,
                'secondary' => $this->secondary_button_text,
                'url' => $this->button_url,
            ],
            'media' => [
                'type' => $this->media_type,
                'path' => $this->media_path,
            ],
            'form' => [
                'enabled' => $this->form_enabled,
                'fields' => $this->form_fields,
            ],
            'display' => [
                'pages' => $this->relationLoaded('pages')
                    ? $this->pages->pluck('slug')->values()->all()
                    : [],
                'audience_type' => $this->audience_type,
            ],
            'trigger' => [
                'type' => $this->trigger_type,
                'value' => $this->trigger_value,
            ],
            'frequency' => [
                'show_every' => $this->show_every,
                'max_impressions' => $this->max_impressions,
            ],
            'scoped_to_entities' => (int) ($this->attachable_links_count ?? 0) > 0,
            'products' => ProductAllResource::collection($this->relationLoaded('products') ? $this->products : collect()),
            'restaurants' => ShopAllResource::collection($this->relationLoaded('restaurants') ? $this->restaurants : collect()),
            'serviceProviders' => ShopAllResource::collection($this->relationLoaded('serviceProviders') ? $this->serviceProviders : collect()),
            'shops' => ShopAllResource::collection($this->relationLoaded('stores') ? $this->stores : collect()),
            'recipes' => RecipeAllResource::collection($this->relationLoaded('recipes') ? $this->recipes : collect()),
            'baskets' => BasketAllResource::collection($this->relationLoaded('baskets') ? $this->baskets : collect()),
            'shop_vendor_services' => ShopVendorServiceAllResource::collection(
                $this->relationLoaded('shopVendorServices') ? $this->shopVendorServices : collect()
            ),
            'promotions' => PromotionUserOneResource::collection($this->relationLoaded('promotions') ? $this->promotions : collect()),
        ];
    }
}
