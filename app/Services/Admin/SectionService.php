<?php

namespace App\Services\Admin;

use App\Http\Resources\Section\AllResource;
use App\Http\Resources\Section\OneResource;
use App\Models\DisplayType;
use App\Models\Page;
use App\Models\Section;
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
        $this->pagination = true;
        $this->relations = ['sectionItems.item'];
        $this->syncRelations = [
            'sectionItems'   => 'item_ids',
        ];
    }

    public function displayTypes(string $manualModel, int $pageId): Collection
    {
        $page = Page::query()->find($pageId);

        if (!$page) {
            return new Collection();
        }

        return DisplayType::query()
            ->where('manual_model', $manualModel)
            ->where(function ($builder) use ($page) {
                $builder
                    ->whereNull('allowed_page_slugs')
                    ->orWhereJsonLength('allowed_page_slugs', 0)
                    ->orWhereJsonContains('allowed_page_slugs', $page->slug);
            })
            ->get();
    }
}
