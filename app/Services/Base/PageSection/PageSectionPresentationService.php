<?php

namespace App\Services\Base\PageSection;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PageSectionPresentationService
{
    /**
     * Query keys that may be provided at request time (e.g. dynamic category pages)
     * and forwarded into each section's API filters.
     */
    private const RUNTIME_FILTER_KEYS = [
        'category_id',
        'parent_id',
        'brand_id',
        'shop_id',
        'type',
        'sort_by',
        'price_min',
        'price_max',
    ];

    /**
     * Which runtime filter keys each API section type can safely consume.
     * This prevents forwarding a key to a handler whose table does not support it
     * (e.g. passing category_id to a shops/brands section would break the query).
     */
    private const RUNTIME_FILTER_SUPPORT = [
        'products' => ['category_id', 'brand_id', 'shop_id', 'type', 'sort_by', 'price_min', 'price_max'],
        'suggested_products' => ['category_id', 'brand_id', 'shop_id', 'type'],
        'categories' => ['parent_id', 'brand_id', 'shop_id', 'type', 'sort_by'],
        'shops' => ['brand_id'],
        'restaurants' => ['brand_id'],
        'suggested_shops' => ['brand_id'],
        'brands' => [],
        'recipes' => [],
        'baskets' => [],
        'suggested_baskets' => [],
        'schedule-basket' => [],
    ];

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

        $runtimeFilters = $this->extractRuntimeFilters($request);

        $sections = $sections
            ->filter(fn ($pageSection) => $this->matchesShowWhen($pageSection->show_when ?? [], $request))
            ->each(fn ($pageSection) => $this->applyRuntimeFilters($pageSection, $runtimeFilters))
            ->values();

        return $this->mergeBannerPageSections($sections);
    }

    /**
     * Whitelisted request query filters that can drive dynamic pages (e.g. category pages).
     */
    private function extractRuntimeFilters(Request $request): array
    {
        $filters = [];

        foreach (self::RUNTIME_FILTER_KEYS as $key) {
            $value = $request->query($key);

            if ($value !== null && $value !== '') {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }

    /**
     * Merge runtime filters into a page section's stored filters.
     * Only keys supported by the section's API type are forwarded, and admin-configured
     * (stored) filters always take precedence over request values.
     */
    private function applyRuntimeFilters(PageSection $pageSection, array $runtimeFilters): void
    {
        if (empty($runtimeFilters)) {
            return;
        }

        $section = $pageSection->section;

        // Runtime filters only apply to API sections (manual sections use stored items).
        if (!$section || $section->type !== 'api') {
            return;
        }

        $supportedKeys = self::RUNTIME_FILTER_SUPPORT[$section->api_method] ?? [];

        if (empty($supportedKeys)) {
            return;
        }

        $applicable = array_intersect_key($runtimeFilters, array_flip($supportedKeys));

        if (empty($applicable)) {
            return;
        }

        $stored = $pageSection->filters ?? [];

        $pageSection->filters = array_merge($applicable, $stored);
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
