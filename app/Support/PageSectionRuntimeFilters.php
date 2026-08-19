<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Extracts URL query filters for page sections and maps them per api_method.
 */
class PageSectionRuntimeFilters
{
    /** Query keys accepted from page URLs (same family as /user/products). */
    private const QUERY_KEYS = [
        'category_id',
        'parent_id',
        'brand_id',
        'shop_id',
        'country_id',
        'country',
        'name',
        'price_min',
        'price_max',
        'on_sale',
        'in_stock_only',
        'is_free_delivery',
        'is_instant_delivery',
        'type',
        'search',
        'sort_by',
    ];

    /** Which query keys each API section handler can consume. */
    private const SUPPORT_BY_API_METHOD = [
        'products' => [
            'category_id',
            'brand_id',
            'shop_id',
            'country_id',
            'country',
            'name',
            'price_min',
            'price_max',
            'on_sale',
            'in_stock_only',
            'is_free_delivery',
            'is_instant_delivery',
            'attribute_values',
            'type',
            'search',
            'sort_by',
        ],
        'suggested_products' => [
            'category_id',
            'brand_id',
            'shop_id',
            'country_id',
            'country',
            'name',
            'type',
            'search',
            'sort_by',
        ],
        'categories' => ['parent_id', 'brand_id', 'shop_id', 'name', 'type', 'sort_by'],
        'shops' => ['brand_id', 'shop_id', 'name', 'type', 'sort_by'],
        'restaurants' => ['brand_id', 'name', 'type', 'sort_by'],
        'suggested_shops' => ['brand_id', 'type'],
        'brands' => ['name', 'type', 'sort_by'],
        'recipes' => ['name', 'type', 'sort_by'],
        'baskets' => ['category_id', 'name', 'type', 'sort_by'],
        'suggested_baskets' => ['category_id', 'type'],
        'schedule-basket' => ['category_id', 'type'],
    ];

    public static function extractFromRequest(Request $request): array
    {
        $filters = [];

        foreach (self::QUERY_KEYS as $key) {
            $value = $request->query($key);

            if ($value !== null && $value !== '') {
                $filters[$key] = $value;
            }
        }

        if ($request->has('attribute_values')) {
            $attributeValues = $request->query('attribute_values');

            if (is_string($attributeValues)) {
                $attributeValues = array_map('intval', array_filter(explode(',', $attributeValues)));
            }

            if (is_array($attributeValues) && $attributeValues !== []) {
                $filters['attribute_values'] = array_values(array_map('intval', $attributeValues));
            }
        }

        if ($request->filled('sortField')) {
            $filters['sort_by'] = self::mapSortField(
                (string) $request->query('sortField'),
                (string) $request->query('sortOrder', 'asc'),
            );
        }

        return $filters;
    }

    /**
     * Merge section baseline filters with URL overrides (URL wins on conflict).
     *
     * @return array<string, mixed>
     */
    public static function mergeForApiSection(string $apiMethod, array $storedFilters, array $runtimeFilters): array
    {
        $supportedKeys = self::SUPPORT_BY_API_METHOD[$apiMethod] ?? [];

        if ($supportedKeys === []) {
            return $storedFilters ?? [];
        }

        $applicable = array_intersect_key(
            $runtimeFilters,
            array_flip($supportedKeys),
        );

        return array_merge($storedFilters ?? [], $applicable);
    }

    public static function mapSortField(string $sortField, string $sortOrder): string
    {
        $direction = strtolower($sortOrder) === 'desc' ? 'desc' : 'asc';
        $field = strtolower(trim($sortField));

        return match ($field) {
            'price' => $direction === 'desc' ? 'price_desc' : 'price_asc',
            'rating', 'rate' => $direction === 'desc' ? 'rating_desc' : 'rating_asc',
            'created_at', 'created', 'newest', 'new' => $direction === 'desc' ? 'newest' : 'oldest',
            default => in_array($field, ['price_desc', 'price_asc', 'newest', 'oldest', 'rating_desc', 'rating_asc', 'rating'], true)
                ? $field
                : ($direction === 'desc' ? 'newest' : 'oldest'),
        };
    }
}
