<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class AttributeValue extends Model
{
    use HasFactory, HasTranslations, SoftDeletes, LogsActivity;

    protected $table = 'attribute_values';

    protected $fillable = [
        'category_attribute_id',
        'name',
        'color_id',
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

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
}


