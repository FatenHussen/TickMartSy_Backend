<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class CategoryAttribute extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $table = 'category_attributes';

    protected $fillable = [
        'category_id',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public $translatable = [
        'name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function scopeForCategoryTree($query, ?int $categoryId)
    {
        $rootId = Category::resolveRootId($categoryId);

        if (!$rootId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('category_id', $rootId);
    }
}
