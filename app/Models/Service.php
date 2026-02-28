<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Service extends Model
{
    use HasTranslations, LogsActivity;
    protected $fillable = ['name'];
    public $translatable = ['name'];
}
