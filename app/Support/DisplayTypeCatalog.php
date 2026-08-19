<?php

namespace App\Support;

use App\Models\Section;

class DisplayTypeCatalog
{
    public static function idFor(string $manualModel, ?string $pageSlug = null): ?int
    {
        if ($pageSlug !== null) {
            $override = config("display_types.page_slug_overrides.{$pageSlug}.{$manualModel}");

            if ($override !== null) {
                return (int) $override;
            }
        }

        $id = config("display_types.ids.{$manualModel}");

        return $id !== null ? (int) $id : null;
    }

    public static function idForSection(Section $section, ?string $pageSlug = null): ?int
    {
        $contentType = $section->contentType();

        if (!$contentType) {
            return null;
        }

        if ($contentType === 'restaurant') {
            $contentType = 'shop';
        }

        if ($section->api_method === 'schedule-basket') {
            $contentType = 'schedule-basket';
        }

        return self::idFor($contentType, $pageSlug);
    }
}
