<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class CategoryAttribute extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'category_attributes';

    protected $fillable = [
        'category_id',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
