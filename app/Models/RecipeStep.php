<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class RecipeStep extends Model
{
    use HasTranslations;
    public $translatable = [
        'heat_level',
        'time_minutes',
        'instruction'
    ];

    protected $fillable = [
        'heat_level',
        'time_minutes',
        'instruction',
        'step_number',
        'recipe_id'
    ];
}
