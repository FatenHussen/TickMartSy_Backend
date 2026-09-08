<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class NavMenuItem extends Model
{
    use HasTranslations;

    /** Destination types the dashboard can choose from. */
    public const TYPES = ['page', 'category', 'brand', 'url', 'route'];

    /**
     * Predefined app destinations (fixed screens that are not a page/category/brand),
     * e.g. the top bar links: baskets, points, help, subscriptions, shops...
     */
    public const ROUTE_KEYS = [
        'home',
        'categories',
        'brands',
        'shops',
        'baskets',
        'schedules',
        'points',
        'help',
        'subscriptions',
    ];

    /** Admin dropdown labels for route_key (جدولة is a screen, not a Page Builder page). */
    public const ROUTE_KEY_LABELS = [
        'home' => ['ar' => 'الرئيسية', 'en' => 'Home'],
        'categories' => ['ar' => 'الفئات الرئيسية', 'en' => 'Main Categories'],
        'brands' => ['ar' => 'الماركات', 'en' => 'Brands'],
        'shops' => ['ar' => 'كل المتاجر', 'en' => 'All shops'],
        'baskets' => ['ar' => 'سلالي', 'en' => 'My baskets'],
        'schedules' => ['ar' => 'جدولة', 'en' => 'Schedules'],
        'points' => ['ar' => 'النقاط والمكافآت', 'en' => 'Points & rewards'],
        'help' => ['ar' => 'المساعدة والدعم', 'en' => 'Help & support'],
        'subscriptions' => ['ar' => 'باقات الاشتراك', 'en' => 'Subscription packages'],
    ];

    public static function routeKeyOptions(): array
    {
        return collect(self::ROUTE_KEYS)->map(fn (string $key) => [
            'key' => $key,
            'label' => self::ROUTE_KEY_LABELS[$key] ?? ['ar' => $key, 'en' => $key],
        ])->values()->all();
    }

    protected $fillable = [
        'title',
        'type',
        'page_id',
        'category_id',
        'brand_id',
        'url',
        'route_key',
        'icon',
        'order',
        'is_active',
        'open_in_new_tab',
    ];

    protected $casts = [
        'title' => 'array',
        'order' => 'integer',
        'is_active' => 'boolean',
        'open_in_new_tab' => 'boolean',
    ];

    public array $translatable = ['title'];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset('storage/' . $this->icon) : null;
    }
}
