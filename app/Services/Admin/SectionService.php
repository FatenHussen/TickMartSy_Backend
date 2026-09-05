<?php

namespace App\Services\Admin;

use App\Enums\SectionLayout;
use App\Enums\VariantSection;
use App\Http\Resources\Section\AllResource;
use App\Http\Resources\Section\OneResource;
use App\Models\Page;
use App\Models\Section;
use App\Support\DisplayTypeCatalog;
use Illuminate\Database\Eloquent\Collection;
use App\Services\BaseService;

class SectionService extends BaseService
{
    public function __construct(Section $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name'];
        $this->relations = ['sectionItems.item'];
        $this->syncRelations = [
            'sectionItems'   => 'item_ids',
        ];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['content_type'])) {
            $contentType = $filters['content_type'];
            $query->where(function ($builder) use ($contentType) {
                $builder->where('manual_model', $contentType);

                $apiMethod = Section::API_METHOD_BY_CONTENT[$contentType] ?? null;
                if ($apiMethod) {
                    $builder->orWhere('api_method', $apiMethod);
                }
            });
            unset($filters['content_type']);
        }

        $query = parent::queryBuilder($query, $filters, $config);

        return $query->withCount('pages');
    }

    public function create($data)
    {
        $data = $this->normalizeSliderPayload($data);

        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $data = $this->normalizeSliderPayload($data);

        return parent::update($id, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeSliderPayload(array $data): array
    {
        unset($data['content_type']);

        if (($data['type'] ?? null) === 'manual' && !empty($data['item_ids']) && is_array($data['item_ids'])) {
            $itemType = Section::itemTypeFor($data['manual_model'] ?? null);
            if ($itemType) {
                foreach ($data['item_ids'] as &$item) {
                    if (is_array($item) && empty($item['item_type'])) {
                        $item['item_type'] = $itemType;
                    }
                }
                unset($item);
            }
        }

        $data['layout'] = $data['layout'] ?? SectionLayout::Slider->value;
        $data['variant'] = $data['variant'] ?? VariantSection::Horizontal->value;

        if (($data['api_method'] ?? null) === 'restaurants') {
            $filters = $data['filters'] ?? [];
            $filters['is_restaurant'] = true;
            $data['filters'] = $filters;
        }

        return $data;
    }

    public function displayTypes(string $manualModel, int $pageId): Collection
    {
        $page = Page::query()->find($pageId);

        if (!$page) {
            return new Collection();
        }

        return DisplayTypeCatalog::allowedFor($manualModel, $page->slug);
    }
}
