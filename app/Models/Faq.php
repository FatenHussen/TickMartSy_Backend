<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Faq extends Model
{
    use HasTranslations, LogsActivity;

    protected $fillable = [
        'question',
        'answer',
        'type'
    ];

    public array $translatable = ['question', 'answer'];
}
