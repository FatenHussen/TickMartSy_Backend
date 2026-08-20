<?php

namespace App\Services\Admin;

use App\Http\Resources\PageSection\AdminOneResource;
use App\Http\Resources\PageSection\AllResource;
use App\Http\Resources\PageSection\OneResource;
use App\Models\DisplayType;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Section;
use App\Support\DisplayTypeCatalog;
use App\Services\Base\PageSection\PageSectionPresentationService;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PageSectionService extends BaseService
{

    public function __construct(PageSection $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name'];
        $this->syncRelations = [
            'sectionItems'   => 'item_ids',
        ];
    }

    public function displayTypes($manual_model)
    {
        return DisplayType::where('manual_model', $manual_model)->select('id', 'image')->get();
    }

    /**
     * Preview page sections exactly as the user/mobile API renders them.
     */
    public function previewForPage(int $pageId, Request $request): array
    {
        $page = Page::query()->findOrFail($pageId);

        $sections = app(PageSectionPresentationService::class)
            ->getSectionsForUserView($page, $request);

        return [
            'page' => [
                'id' => $page->id,
                'slug' => $page->slug,
                'title' => $page->title,
                'filters' => $page->filters,
            ],
            'sections' => OneResource::collection($sections),
        ];
    }

    public function reorderForPage(int $pageId, array $sections): int
    {
        return DB::transaction(function () use ($pageId, $sections) {
            $ids = array_column($sections, 'id');

            $existingIds = PageSection::query()
                ->where('page_id', $pageId)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            if (count($existingIds) !== count($ids)) {
                throw ValidationException::withMessages([
                    'sections' => __('Some page sections do not belong to this page.'),
                ]);
            }

            $updated = 0;
            foreach ($sections as $item) {
                $updated += PageSection::query()
                    ->where('id', $item['id'])
                    ->where('page_id', $pageId)
                    ->update([
                        'order' => $item['order'],
                        'position' => $item['position'],
                    ]);
            }

            return $updated;
        });
    }

    public function create($data)
    {
        $data = $this->injectDisplayTypeId($data);

        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $pageSection = PageSection::query()->findOrFail($id);

        $data = $this->injectDisplayTypeId($data, $pageSection);

        return parent::update($id, $data);
    }

    private function injectDisplayTypeId(array $data, ?PageSection $pageSection = null): array
    {
        // Client must not own this field — always resolve from section content type.
        unset($data['display_type_id']);

        $sectionId = (int) ($data['section_id'] ?? $pageSection?->section_id);
        $pageId = (int) ($data['page_id'] ?? $pageSection?->page_id);

        if (!$sectionId || !$pageId) {
            return $data;
        }

        $section = Section::query()->find($sectionId);
        $page = Page::query()->find($pageId);

        if (!$section || !$page) {
            return $data;
        }

        // Keep existing value on update unless section/page changed.
        if ($pageSection?->display_type_id
            && (int) $pageSection->section_id === $sectionId
            && (int) $pageSection->page_id === $pageId
        ) {
            return $data;
        }

        DisplayTypeCatalog::ensureSeeded();

        $displayTypeId = DisplayTypeCatalog::resolveForSection($section, $page);

        if (!$displayTypeId || !DisplayType::query()->whereKey($displayTypeId)->exists()) {
            throw ValidationException::withMessages([
                'display_type_id' => __('Display types are missing. Run: php artisan db:seed --class=DisplayTypeSeeder'),
            ]);
        }

        $data['display_type_id'] = $displayTypeId;

        return $data;
    }
}
