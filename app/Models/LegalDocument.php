<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class LegalDocument extends Model
{
    use HasTranslations, LogsActivity;
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
