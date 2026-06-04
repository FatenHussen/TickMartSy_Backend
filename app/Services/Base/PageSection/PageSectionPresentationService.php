<?php

namespace App\Services\Base\PageSection;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PageSectionPresentationService
{
    /**
     * Sections for a page as the mobile/user API returns them (active only, show_when, banner merge).
     */
    public function getSectionsForUserView(Page $page, Request $request): Collection
    {
        $sections = $page->pageSections()
            ->where('page_sections.is_active', true)
            ->whereHas('section', fn ($query) => $query->where('is_active', true))
            ->with('section.sectionItems.item')
            ->get();

        $sections = $sections
            ->filter(fn ($pageSection) => $this->matchesShowWhen($pageSection->show_when ?? [], $request))
            ->values();

        return $this->mergeBannerPageSections($sections);
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

    /**
     * Collapse multiple manual banner page sections into one block (the first in order)
     * with all banner section items concatenated.
     */
    private function mergeBannerPageSections(Collection $pageSections): Collection
    {
        $bannerKeys = [];
        foreach ($pageSections as $key => $pageSection) {
            if ($this->isBannerPageSection($pageSection)) {
                $bannerKeys[] = $key;
            }
        }

        if (count($bannerKeys) <= 1) {
            return $pageSections;
        }

        $firstKey = $bannerKeys[0];
        $firstPageSection = $pageSections[$firstKey];

        $mergedItems = collect($bannerKeys)
            ->flatMap(fn ($key) => $pageSections[$key]->section->sectionItems ?? collect())
            ->values();

        $sectionWithMergedItems = clone $firstPageSection->section;
        $sectionWithMergedItems->setRelation('sectionItems', $mergedItems);
        $firstPageSection->setRelation('section', $sectionWithMergedItems);

        return $pageSections
            ->filter(function ($pageSection, $key) use ($firstKey) {
                if (!$this->isBannerPageSection($pageSection)) {
                    return true;
                }

                return $key === $firstKey;
            })
            ->values();
    }

    private function isBannerPageSection(PageSection $pageSection): bool
    {
        $section = $pageSection->section;

        return $section !== null
            && $section->type === 'manual'
            && ($section->manual_model ?? null) === 'banner';
    }
}
