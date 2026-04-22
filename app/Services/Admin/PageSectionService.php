<?php

namespace App\Services\Admin;

use App\Http\Resources\PageSection\AdminOneResource;
use App\Http\Resources\PageSection\AllResource;
use App\Models\DisplayType;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Section;
use App\Services\BaseService;
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
        unset($data['display_type_id']);

        $sectionId = (int) ($data['section_id'] ?? $pageSection?->section_id);
        $pageId = (int) ($data['page_id'] ?? $pageSection?->page_id);

        $displayTypeId = $this->resolveDisplayTypeId($sectionId, $pageId);

        if (!$displayTypeId) {
            throw ValidationException::withMessages([
                'display_type_id' => __('Could not resolve display type for the selected section/page.'),
            ]);
        }

        $data['display_type_id'] = $displayTypeId;

        return $data;
    }

    private function resolveDisplayTypeId(int $sectionId, int $pageId): ?int
    {
        if (!$sectionId || !$pageId) {
            return null;
        }

        $section = Section::query()->find($sectionId);
        $page = Page::query()->find($pageId);

        if (!$section || !$page || !$section->manual_model) {
            return null;
        }

        $displayTypes = DisplayType::query()
            ->where('manual_model', $section->manual_model)
            ->get();

        if ($displayTypes->isEmpty()) {
            return null;
        }

        $matchedDisplayType = $displayTypes->first(function (DisplayType $displayType) use ($page): bool {
            $allowedPageSlugs = $displayType->allowed_page_slugs ?? [];

            return !empty($allowedPageSlugs) && in_array($page->slug, $allowedPageSlugs, true);
        });

        if ($matchedDisplayType) {
            return $matchedDisplayType->id;
        }

        $defaultDisplayType = $displayTypes->first(function (DisplayType $displayType): bool {
            return empty($displayType->allowed_page_slugs ?? []);
        });

        return $defaultDisplayType?->id ?? $displayTypes->first()?->id;
    }
}
