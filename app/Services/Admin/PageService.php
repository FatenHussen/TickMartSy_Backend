<?php

namespace App\Services\Admin;

use App\Enums\VariantSection;
use App\Exceptions\NotFoundException;
use App\Http\Resources\Page\AllResource;
use App\Http\Resources\Page\OneResource;
use App\Http\Resources\PageSection\AdminOneResource;
use App\Models\DisplayType;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Section;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class PageService extends BaseService
{
    public function __construct(Page $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'title', 'slug'];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        return $query->withCount('pageSections');
    }

    public function getOne($id)
    {
        $page = Page::query()
            ->with([
                'pageSections' => fn ($query) => $query->orderBy('order'),
                'pageSections.section',
                'pageSections.page',
            ])
            ->find($id);

        if (!$page) {
            throw new NotFoundException();
        }

        return new OneResource($page);
    }

    /**
     * Create a Section and attach it to the page (PageSection) in one transaction.
     * This is what powers the unified "add block" action in the dashboard.
     */
    public function addSection(int $pageId, array $data): AdminOneResource
    {
        $page = Page::query()->findOrFail($pageId);

        return DB::transaction(function () use ($page, $data) {
            $section = $this->createSectionFromPayload($data);

            $pageSection = PageSection::create([
                'page_id' => $page->id,
                'section_id' => $section->id,
                'name' => $data['name'] ?? null,
                'position' => $data['position'] ?? 'after',
                'order' => $data['order'] ?? $this->nextOrderForPage($page->id),
                'variant' => $data['variant'] ?? VariantSection::Horizontal->value,
                'background_color' => $data['background_color'] ?? null,
                'background_card_color' => $data['background_card_color'] ?? null,
                'filters' => $data['filters'] ?? null,
                'show_when' => $data['show_when'] ?? null,
                'display_type_id' => $this->resolveDisplayTypeId($section, $page),
                'is_active' => true,
            ]);

            $pageSection->load('section', 'page');

            return new AdminOneResource($pageSection);
        });
    }

    private function createSectionFromPayload(array $data): Section
    {
        $isManual = ($data['type'] ?? 'manual') === 'manual';

        $section = Section::create([
            'name' => $data['name'] ?? null,
            'type' => $isManual ? 'manual' : 'api',
            'manual_model' => $isManual ? ($data['manual_model'] ?? null) : null,
            'api_method' => $isManual ? null : ($data['api_method'] ?? null),
            'filters' => $data['filters'] ?? null,
            'is_active' => true,
        ]);

        if ($isManual && !empty($data['item_ids'])) {
            $itemType = config("section_items.{$data['manual_model']}.item_type");

            foreach ($data['item_ids'] as $index => $item) {
                $section->sectionItems()->create([
                    'item_type' => $itemType,
                    'item_id' => $item['item_id'],
                    'link' => $item['link'] ?? null,
                    'order' => $item['order'] ?? $index,
                ]);
            }
        }

        return $section;
    }

    private function nextOrderForPage(int $pageId): int
    {
        return (int) PageSection::query()->where('page_id', $pageId)->max('order') + 1;
    }

    /**
     * Resolve a display type for manual sections. API sections render without one.
     */
    private function resolveDisplayTypeId(Section $section, Page $page): ?int
    {
        if ($section->type !== 'manual' || !$section->manual_model) {
            return null;
        }

        $displayTypes = DisplayType::query()
            ->where('manual_model', $section->manual_model)
            ->get();

        if ($displayTypes->isEmpty()) {
            return null;
        }

        $matched = $displayTypes->first(function (DisplayType $displayType) use ($page): bool {
            $allowed = $displayType->allowed_page_slugs ?? [];

            return !empty($allowed) && in_array($page->slug, $allowed, true);
        });

        if ($matched) {
            return $matched->id;
        }

        $default = $displayTypes->first(fn (DisplayType $displayType) => empty($displayType->allowed_page_slugs ?? []));

        return $default?->id ?? $displayTypes->first()?->id;
    }
}
