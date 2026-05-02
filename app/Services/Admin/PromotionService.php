<?php

namespace App\Services\Admin;

use App\Http\Resources\Promotion\AllResource;
use App\Http\Resources\Promotion\OneResource;
use App\Models\Page;
use App\Models\Promotion;
use App\Services\BaseService;
use Illuminate\Support\Arr;

class PromotionService extends BaseService
{
    public function __construct(Promotion $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['pages'];
    }

    public function create($data)
    {
        $pageSlugs = $this->extractPageSlugs($data);

        $object = $this->model::create($data);
        $this->handleSingleImages($object, $data);
        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);

        if ($pageSlugs !== null) {
            $this->syncPagesForPromotion($object, $pageSlugs);
        }

        $object->refresh()->load('pages');

        return new $this->resource($object);
    }

    public function update($id, array $data)
    {
        $pageSlugs = $this->extractPageSlugs($data);

        $resource = parent::update($id, $data);

        if ($pageSlugs !== null) {
            $model = Promotion::query()->findOrFail($id);
            $this->syncPagesForPromotion($model, $pageSlugs);
            $model->refresh()->load('pages');

            return new $this->resource($model);
        }

        return $resource;
    }

    /**
     * @return list<string>|null null when the client omitted `page_slugs`
     */
    protected function extractPageSlugs(array &$data): ?array
    {
        if (!array_key_exists('page_slugs', $data)) {
            return null;
        }

        $raw = $data['page_slugs'];
        unset($data['page_slugs']);

        return array_values(array_unique(array_filter(
            array_map('strval', Arr::wrap($raw)),
            static fn (string $slug) => $slug !== ''
        )));
    }

    /**
     * @param  list<string>  $slugs
     */
    protected function syncPagesForPromotion(Promotion $promotion, array $slugs): void
    {
        if ($slugs === []) {
            $promotion->pages()->detach();

            return;
        }

        $ids = Page::query()->whereIn('slug', $slugs)->pluck('id')->all();
        $promotion->pages()->sync($ids);
    }

    public function fieldsForType(string $type): array
    {
        $pages = ['page_slugs'];

        return match ($type) {
            'simple_discount' => [
                'name',
                'description',
                'discount_value',
                'discount_type',
                'is_active',
                'starts_at',
                'ends_at',
                ...$pages,
            ],
            'spend_x_discount' => [
                'name',
                'description',
                'min_spend',
                'discount_value',
                'discount_type',
                'is_active',
                'starts_at',
                'ends_at',
                ...$pages,
            ],
            'spend_x_get_gift' => [
                'name',
                'description',
                'min_spend',
                'gift_description',
                'is_active',
                'starts_at',
                'ends_at',
                ...$pages,
            ],
            'spend_x_get_points' => [
                'name',
                'description',
                'min_spend',
                'reward_points',
                'is_active',
                'starts_at',
                'ends_at',
                ...$pages,
            ],
            'free_shipping' => [
                'name',
                'description',
                'is_active',
                'starts_at',
                'ends_at',
                ...$pages,
            ],
            'spend_x_get_free_shipping' => [
                'name',
                'description',
                'min_spend',
                'is_active',
                'starts_at',
                'ends_at',
                ...$pages,
            ],
            default => ['name', 'description', 'is_active', 'starts_at', 'ends_at', ...$pages],
        };
    }
}
