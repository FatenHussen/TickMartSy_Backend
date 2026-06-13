<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\PopupCampaign\PopupCampaignCollection;
use App\Http\Resources\Admin\PopupCampaign\PopupCampaignResource;
use App\Models\Page;
use App\Models\PopupCampaign;
use App\Services\BaseService;
use Illuminate\Support\Arr;

class PopupCampaignService extends BaseService
{
    public function __construct(PopupCampaign $model)
    {
        $this->model = $model;
        $this->resource = PopupCampaignResource::class;
        $this->collection = PopupCampaignCollection::class;
        $this->sortableFields = ['priority', 'created_at', 'updated_at', 'status'];
        $this->searchableFields = ['title', 'headline', 'description'];
        $this->singleImages = ['media_path'];
        $this->relations = ['products', 'shops', 'recipes', 'baskets', 'pages', 'promotions'];
    }

    public function create($data)
    {
        $attachables = $this->extractAttachablesFromPayload($data);
        $pageSlugs = $this->extractShowOnPagesSlugs($data);
        $promotionIds = $this->extractPromotionIds($data);
        $data = $this->withFixedFrequency($data);

        $object = $this->model::create($data);
        $this->handleSingleImages($object, $data);
        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);
        $this->performAttachableSync($object, $attachables);

        if ($pageSlugs !== null) {
            $this->syncPagesForPopup($object, $pageSlugs);
        }

        if ($promotionIds !== null) {
            $this->syncPromotionsForPopup($object, $promotionIds);
        }

        $object->refresh()->load(['pages', 'promotions']);

        return new $this->resource($object);
    }

    public function update($id, array $data)
    {
        $attachables = $this->extractAttachablesFromPayload($data);
        $pageSlugs = $this->extractShowOnPagesSlugs($data);
        $promotionIds = $this->extractPromotionIds($data);
        $data = $this->withFixedFrequency($data);
        parent::update($id, $data);
        $model = PopupCampaign::query()->findOrFail($id);
        $this->performAttachableSync($model, $attachables);

        if ($pageSlugs !== null) {
            $this->syncPagesForPopup($model, $pageSlugs);
        }

        if ($promotionIds !== null) {
            $this->syncPromotionsForPopup($model, $promotionIds);
        }

        return new $this->resource($model->refresh()->load(['pages', 'promotions']));
    }

    protected function withFixedFrequency(array $data): array
    {
        $data['show_every'] = PopupCampaign::DEFAULT_SHOW_EVERY;
        $data['max_impressions'] = PopupCampaign::DEFAULT_MAX_IMPRESSIONS;

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractAttachablesFromPayload(array &$data): array
    {
        $keys = ['product_ids', 'shop_ids', 'recipe_ids', 'basket_ids'];
        $instructions = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $instructions[$key] = $data[$key];
                unset($data[$key]);
            }
        }

        return $instructions;
    }

    /**
     * @return list<string>|null null when the client omitted `show_on_pages`
     */
    protected function extractShowOnPagesSlugs(array &$data): ?array
    {
        if (!array_key_exists('show_on_pages', $data)) {
            return null;
        }

        $raw = $data['show_on_pages'];
        unset($data['show_on_pages']);

        if (!is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('strval', $raw),
            static fn (string $slug) => $slug !== ''
        )));
    }

    /**
     * @return list<int>|null null when the client omitted `promotion_ids`
     */
    protected function extractPromotionIds(array &$data): ?array
    {
        if (!array_key_exists('promotion_ids', $data)) {
            return null;
        }

        $raw = $data['promotion_ids'];
        unset($data['promotion_ids']);

        if (!is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            'intval',
            $raw
        ), static fn (int $id) => $id > 0)));
    }

    /**
     * @param  list<string>  $slugs
     */
    protected function syncPagesForPopup(PopupCampaign $campaign, array $slugs): void
    {
        if ($slugs === []) {
            $campaign->pages()->detach();

            return;
        }

        $ids = Page::query()->whereIn('slug', $slugs)->pluck('id')->all();
        $campaign->pages()->sync($ids);
    }

    /**
     * @param  list<int>  $ids
     */
    protected function syncPromotionsForPopup(PopupCampaign $campaign, array $ids): void
    {
        $campaign->promotions()->sync($ids);
    }

    protected function performAttachableSync(PopupCampaign $campaign, array $instructions): void
    {
        $map = [
            'product_ids' => 'products',
            'shop_ids' => 'shops',
            'recipe_ids' => 'recipes',
            'basket_ids' => 'baskets',
        ];

        foreach ($map as $payloadKey => $relation) {
            if (!array_key_exists($payloadKey, $instructions)) {
                continue;
            }

            $ids = array_values(array_unique(array_filter(array_map(
                'intval',
                Arr::wrap($instructions[$payloadKey])
            ), static fn (int $id) => $id > 0)));

            $campaign->{$relation}()->sync($ids);
        }
    }
}
