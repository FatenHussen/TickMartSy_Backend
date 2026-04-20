<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class CategoryDetail extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'category_id',
        'name',
        'value_options',
        'is_active',
    ];

    protected $casts = [
        'value_options' => 'array',
        'is_active' => 'boolean',
    ];

    public array $translatable = [
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

    public function products()
    {
        return $this->hasMany(ProductCategoryDetail::class);
    }
}
