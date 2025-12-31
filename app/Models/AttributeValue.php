<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class AttributeValue extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'attribute_values';

    protected $fillable = [
        'category_attribute_id',
        'name',
    ];

    public array $translatable = [
        'name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function categoryAttribute()
    {
        return $this->belongsTo(CategoryAttribute::class);
    }
}
