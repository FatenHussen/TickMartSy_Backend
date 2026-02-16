<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class LegalDocument extends Model
{
    use HasTranslations;
    protected $fillable = [
        'key',
        'title',
        'content',
    ];

    public array $translatable = [
        'title',
        'content',
    ];
}
