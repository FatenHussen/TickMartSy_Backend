<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisplayType extends Model
{
    protected $fillable = ['name', 'preview_image', 'fields'];
    protected $casts = ['fields' => 'array'];
}
