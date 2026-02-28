<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Badge extends Model
{
    use HasTranslations, LogsActivity;
    public array $translatable = ['name'];

    protected $fillable = ['name', 'color'];
}
