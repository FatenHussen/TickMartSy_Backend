<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Page extends Model
{
    protected $casts = [
        'filters' => 'array',
    ];

    protected $fillable = ['title', 'slug', 'filters'];

    public function pageSections()
    {
        return $this->hasMany(PageSection::class)->orderBy('order');
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
