<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Icon extends Model
{
    use LogsActivity, HasTranslations;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'image',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'icon_product');
    }

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
