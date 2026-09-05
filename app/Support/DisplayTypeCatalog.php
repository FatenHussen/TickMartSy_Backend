<?php

namespace App\Support;

use App\Models\DisplayType;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class DisplayTypeCatalog
{
    private static bool $ensured = false;

    /**
     * Ensure display_types table exists and has the canonical rows.
     * Safe to call repeatedly (local DBs sometimes miss this table).
     */
    public static function ensureSeeded(): void
    {
        if (self::$ensured && self::isCatalogReady()) {
            return;
        }

        if (!Schema::hasTable('display_types')) {
            Schema::create('display_types', function ($table) {
                $table->id();
                $table->string('manual_model');
                $table->string('image')->nullable();
                $table->json('fields')->nullable();
                $table->json('allowed_page_slugs')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('display_types', 'allowed_page_slugs')) {
            Schema::table('display_types', function ($table) {
                $table->json('allowed_page_slugs')->nullable()->after('fields');
            });
        }

        $now = now();

        foreach (config('display_types.seed', []) as $id => $attributes) {
            // forceFill: DisplayType::$fillable does not include `id`, and updateOrCreate
            // would otherwise assign auto-increment IDs that drift from config('display_types.ids').
            $row = DisplayType::query()->find($id) ?? new DisplayType;
            $row->forceFill([
                'id' => (int) $id,
                'manual_model' => $attributes['manual_model'],
                'image' => $attributes['image'] ?? null,
                'fields' => $attributes['fields'] ?? null,
                'allowed_page_slugs' => $attributes['allowed_page_slugs'] ?? null,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $now,
            ]);
            $row->save();
        }

        self::$ensured = self::isCatalogReady();
    }

    /**
     * True when every canonical id from config exists in display_types.
     */
    private static function isCatalogReady(): bool
    {
        if (!Schema::hasTable('display_types')) {
            return false;
        }

        $expectedIds = array_map('intval', array_keys(config('display_types.seed', [])));

        if ($expectedIds === []) {
            return false;
        }

        $found = DisplayType::query()
            ->whereIn('id', $expectedIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return count(array_diff($expectedIds, $found)) === 0;
    }

    public static function idFor(string $manualModel, ?string $pageSlug = null): ?int
    {
        self::ensureSeeded();

        $candidate = null;

        if ($pageSlug !== null) {
            $override = config("display_types.page_slug_overrides.{$pageSlug}.{$manualModel}");

            if ($override !== null) {
                $candidate = (int) $override;
            }
        }

        if ($candidate === null) {
            $id = config("display_types.ids.{$manualModel}");
            $candidate = $id !== null ? (int) $id : null;
        }

        if ($candidate !== null && self::exists($candidate)) {
            return $candidate;
        }

        return self::idFromDatabase($manualModel, $pageSlug);
    }

    public static function manualModelForSection(Section $section): ?string
    {
        $contentType = $section->displayModel()
            ?? $section->manual_model
            ?? $section->contentType();

        if (!$contentType) {
            return null;
        }

        if ($contentType === 'restaurant') {
            $contentType = 'shop';
        }

        if ($section->api_method === 'schedule-basket') {
            $contentType = 'schedule-basket';
        }

        if ($section->api_method === 'schedules') {
            $contentType = 'schedule';
        }

        return $contentType;
    }

    public static function idForSection(Section $section, ?string $pageSlug = null): ?int
    {
        $manualModel = self::manualModelForSection($section);

        return $manualModel ? self::idFor($manualModel, $pageSlug) : null;
    }

    /**
     * Prefer a valid requested id; otherwise fall back to the catalog default.
     * Never returns an id that is missing from display_types.
     */
    public static function resolveForSection(Section $section, Page $page, ?int $requestedId = null): ?int
    {
        self::ensureSeeded();

        $manualModel = self::manualModelForSection($section);

        if (!$manualModel) {
            return null;
        }

        if ($requestedId !== null && self::isAllowedId($requestedId, $manualModel, $page->slug)) {
            return $requestedId;
        }

        return self::idFor($manualModel, $page->slug);
    }

    /**
     * Display types available for a content kind on a page.
     */
    public static function allowedFor(string $manualModel, ?string $pageSlug = null): Collection
    {
        self::ensureSeeded();

        return DisplayType::query()
            ->where('manual_model', $manualModel)
            ->where(function ($builder) use ($pageSlug) {
                $builder
                    ->whereNull('allowed_page_slugs')
                    ->orWhereJsonLength('allowed_page_slugs', 0);

                if ($pageSlug !== null) {
                    $builder->orWhereJsonContains('allowed_page_slugs', $pageSlug);
                }
            })
            ->orderBy('id')
            ->get();
    }

    public static function isAllowedId(int $id, string $manualModel, ?string $pageSlug): bool
    {
        return self::allowedFor($manualModel, $pageSlug)->contains('id', $id);
    }

    private static function exists(int $id): bool
    {
        return DisplayType::query()->whereKey($id)->exists();
    }

    private static function idFromDatabase(string $manualModel, ?string $pageSlug): ?int
    {
        $allowed = self::allowedFor($manualModel, $pageSlug);

        return $allowed->isNotEmpty() ? (int) $allowed->first()->id : null;
    }
}
