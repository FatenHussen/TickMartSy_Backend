<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class AttributeValue extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'attribute_values';

    protected $fillable = [
        'category_attribute_id',
        'name',
    ];

    public $translatable = [
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
    public function attribute()
    {
        return $this->belongsTo(CategoryAttribute::class);
    }
}
