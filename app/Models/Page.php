<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Page extends Model
{
    protected $casts = [
        'filters' => 'array',
    ];

    protected $fillable = ['title', 'slug', 'filters', 'category_id'];

    public function pageSections()
    {
        return $this->hasMany(PageSection::class)->orderBy('order');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** True when this page is the auto-generated page of a category. */
    public function isCategoryPage(): bool
    {
        return $this->category_id !== null;
    }

    /** Only category-backed pages. */
    public function scopeCategoryPages(Builder $query): Builder
    {
        return $query->whereNotNull('category_id');
    }

    /** Only standalone content pages (home, offers, ...), not category pages. */
    public function scopeContentPages(Builder $query): Builder
    {
        return $query->whereNull('category_id');
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class)->withTimestamps();
    }

    public function popupCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(PopupCampaign::class)->withTimestamps();
    }
}
