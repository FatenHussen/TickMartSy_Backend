<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisplayType extends Model
{

    protected $fillable = ['manual_model', 'image', 'fields'];
    protected $casts = ['fields' => 'array'];
    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
