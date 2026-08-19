<?php

namespace App\Services\Admin;

use App\Enums\VariantSection;
use App\Exceptions\NotFoundException;
use App\Http\Resources\Page\AllResource;
use App\Http\Resources\Page\OneResource;
use App\Http\Resources\PageSection\AdminOneResource;
use App\Http\Resources\Section\AllResource as SectionAllResource;
use App\Models\DisplayType;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Section;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PageService extends BaseService
{
    public function __construct(Page $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'slug'];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (array_key_exists('type', $filters)) {
            if ($filters['type'] === 'content') {
                $query->contentPages();
            } elseif ($filters['type'] === 'category') {
                $query->categoryPages();
            }
            unset($filters['type']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
            unset($filters['category_id']);
        }

        if (!empty($config['search'])) {
            $search = strtolower($config['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(id) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(slug) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.ar'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))) LIKE ?", ["%{$search}%"]);
            });
            unset($config['search']);
        }

        $query = parent::queryBuilder($query, $filters, $config);

        return $query->withCount('pageSections');
    }

    public function getOne($id)
    {
        $page = Page::query()
            ->with([
                'category',
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

    public function update($id, array $data)
    {
        $page = Page::query()->findOrFail($id);

        if ($page->isCategoryPage()) {
            throw ValidationException::withMessages([
                'page' => __('Category pages cannot be edited here. Update the category name from Categories, or manage sections inside the page builder.'),
            ]);
        }

        return parent::update($id, $data);
    }

    public function delete($id): bool
    {
        $page = Page::query()->findOrFail($id);

        if ($page->isCategoryPage()) {
            throw ValidationException::withMessages([
                'page' => __('Category pages cannot be deleted directly. Delete the category from Categories to remove its page.'),
            ]);
        }

        return parent::delete($id);
    }

    /**
     * All sliders in the library — used by "Add section" inside a page.
     */
    public function slidersForPage(int $pageId, array $filters = [], array $config = []): array
    {
        Page::query()->findOrFail($pageId);

        $query = Section::query()
            ->where('is_active', true)
            ->withCount('pages');

        if (!empty($config['search'])) {
            $search = strtolower($config['search']);
            $query->where(function ($builder) use ($search) {
                $builder
                    ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$search}%"]);
            });
        }

        if (!empty($filters['content_type'])) {
            $contentType = $filters['content_type'];
            $query->where(function ($builder) use ($contentType) {
                $builder->where('manual_model', $contentType);

                $apiMethod = Section::API_METHOD_BY_CONTENT[$contentType] ?? null;
                if ($apiMethod) {
                    $builder->orWhere('api_method', $apiMethod);
                }
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $perPage = (int) ($config['per_page'] ?? 50);
        $page = (int) ($config['page'] ?? 1);

        $result = $query
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => SectionAllResource::collection($result->items()),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
        ];
    }

    /**
     * Attach an existing slider or create a new one, then link it to the page.
     */
    public function addSection(int $pageId, array $data): AdminOneResource
    {
        $page = Page::query()->findOrFail($pageId);

        return DB::transaction(function () use ($page, $data) {
            if (!empty($data['section_id'])) {
                $section = Section::query()->findOrFail($data['section_id']);
            } else {
                $section = $this->createSectionFromPayload($data);
            }

            $pageSection = PageSection::create([
                'page_id' => $page->id,
                'section_id' => $section->id,
                'name' => $data['name'] ?? $section->getTranslations('name'),
                'position' => $data['position'] ?? 'after',
                'order' => $data['order'] ?? $this->nextOrderForPage($page->id),
                'variant' => $data['variant'] ?? $section->variant ?? VariantSection::Horizontal->value,
                'background_color' => $data['background_color'] ?? $section->background_color,
                'background_card_color' => $data['background_card_color'] ?? $section->background_card_color,
                'filters' => $data['filters'] ?? $section->filters,
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
            'variant' => $data['variant'] ?? VariantSection::Horizontal->value,
            'background_color' => $data['background_color'] ?? null,
            'background_card_color' => $data['background_card_color'] ?? null,
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

    private function resolveDisplayTypeId(Section $section, Page $page): ?int
    {
        if ($section->type !== 'manual' || !$section->manual_model) {
            return null;
        }

        $manualModel = $section->displayModel() ?? $section->manual_model;

        $displayTypes = DisplayType::query()
            ->where('manual_model', $manualModel)
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
