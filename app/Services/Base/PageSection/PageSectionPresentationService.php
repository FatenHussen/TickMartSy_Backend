<?php

namespace App\Services\Base\PageSection;

use App\Models\Page;
use App\Models\PageSection;
use App\Support\PageSectionRuntimeFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PageSectionPresentationService
{
    /**
     * Sections for a page as the mobile/user API returns them (active only, show_when).
     */
    public function getSectionsForUserView(Page $page, Request $request): Collection
    {
        $sections = $page->pageSections()
            ->where('page_sections.is_active', true)
            ->whereHas('section', fn ($query) => $query->where('is_active', true))
            ->with(['section.sectionItems.item', 'page'])
            ->get();

        $runtimeFilters = PageSectionRuntimeFilters::extractFromRequest($request);

        $sections = $sections
            ->filter(fn ($pageSection) => $this->matchesShowWhen($pageSection->show_when ?? [], $request))
            ->each(fn ($pageSection) => $this->applyRuntimeFilters($pageSection, $runtimeFilters))
            ->values();

        return $sections;
    }

    /**
     * Merge URL query filters into API section filters.
     * Manual sections are untouched — their items stay fixed.
     */
    private function applyRuntimeFilters(PageSection $pageSection, array $runtimeFilters): void
    {
        if (empty($runtimeFilters)) {
            return;
        }

        $section = $pageSection->section;

        if (!$section || $section->type !== 'api' || !$section->api_method) {
            return;
        }

        $pageSection->filters = PageSectionRuntimeFilters::mergeForApiSection(
            $section->api_method,
            $pageSection->filters ?? [],
            $runtimeFilters,
        );
    }

    private function matchesShowWhen(array $showWhen, Request $request): bool
    {
        if (empty($showWhen)) {
            return true;
        }

        foreach ($showWhen as $key => $expectedValue) {
            if ((string) $request->query($key) !== (string) $expectedValue) {
                return false;
            }
        }

        return true;
    }
}
