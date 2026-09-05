<?php

namespace App\Models;

use App\Enums\SectionLayout;
use App\Enums\VariantSection;
use App\Services\Base\Section\SectionApiService;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasTranslations, LogsActivity;
    public array $translatable = ['name'];

    public const API_METHODS = [
        'brands',
        'categories',
        'recipes',
        'baskets',
        'schedule-basket',
        'schedules',
        'products',
        'shops',
        'restaurants',
        'suggested_products',
        'suggested_baskets',
        'suggested_shops',
    ];

    public const CONTENT_TYPES = [
        'banner',
        'product',
        'shop',
        'restaurant',
        'brand',
        'category',
        'recipe',
        'basket',
        'schedule',
        'schedule-basket',
        'suggested_products',
        'suggested_shops',
        'suggested_baskets',
    ];

    public const API_METHOD_BY_CONTENT = [
        'product' => 'products',
        'shop' => 'shops',
        'restaurant' => 'restaurants',
        'brand' => 'brands',
        'category' => 'categories',
        'recipe' => 'recipes',
        'basket' => 'baskets',
        'schedule' => 'schedules',
        'schedule-basket' => 'schedule-basket',
        'suggested_products' => 'suggested_products',
        'suggested_shops' => 'suggested_shops',
        'suggested_baskets' => 'suggested_baskets',
    ];

    protected $casts = [
        'filters' => 'array',
        'see_more_params' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'layout' => SectionLayout::Slider->value,
        'variant' => VariantSection::Horizontal->value,
    ];

    protected $fillable = [
        'name',
        'type',
        'api_method',
        'filters',
        'manual_model',
        'layout',
        'variant',
        'background_color',
        'background_card_color',
        'see_more',
        'see_more_slug',
        'details_slug',
        'is_active',
    ];

    /**
     * Map dashboard aliases (Scheduled baskets, suggested-products, …) to a canonical content_type.
     */
    public static function canonicalizeContentType(?string $contentType): ?string
    {
        if ($contentType === null || $contentType === '') {
            return $contentType;
        }

        $key = strtolower(trim($contentType));
        $hyphenated = str_replace([' ', '_'], '-', $key);

        $aliases = [
            'schedule-basket' => 'schedule-basket',
            'scheduled-basket' => 'schedule-basket',
            'scheduled-baskets' => 'schedule-basket',
            'schedules' => 'schedule',
            'schedule-category' => 'schedule',
            'schedule-categories' => 'schedule',
            'suggested-products' => 'suggested_products',
            'suggested-shops' => 'suggested_shops',
            'suggested-baskets' => 'suggested_baskets',
        ];

        if (isset($aliases[$hyphenated])) {
            return $aliases[$hyphenated];
        }

        $fromApiMethod = array_flip(self::API_METHOD_BY_CONTENT);
        if (isset($fromApiMethod[$key]) || isset($fromApiMethod[$hyphenated])) {
            return $fromApiMethod[$key] ?? $fromApiMethod[$hyphenated];
        }

        foreach (self::CONTENT_TYPES as $canonical) {
            if (strtolower($canonical) === $key || str_replace('_', '-', strtolower($canonical)) === $hyphenated) {
                return $canonical;
            }
        }

        return $contentType;
    }

    /**
     * Friendly content kind used by the dashboard (product, restaurant, shop, ...).
     */
    public function contentType(): ?string
    {
        if ($this->manual_model) {
            return $this->manual_model;
        }

        if ($this->api_method === 'restaurants') {
            return 'restaurant';
        }

        if (($this->filters['is_restaurant'] ?? false) && in_array($this->api_method, ['shops', 'suggested_shops'], true)) {
            return 'restaurant';
        }

        $map = array_flip(self::API_METHOD_BY_CONTENT);

        return $map[$this->api_method] ?? $this->api_method;
    }

    public function displayModel(): ?string
    {
        if ($this->manual_model === 'restaurant') {
            return 'shop';
        }

        return $this->manual_model;
    }

    public function apiData(?array $filters = null)
    {
        return app(SectionApiService::class)
            ->preview($this, $filters ?? []);
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'page_sections');
    }

    public function sectionItems()
    {
        return $this->hasMany(SectionItem::class)->orderBy('order');
    }
}
