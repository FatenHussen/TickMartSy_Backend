<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Area extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'lat',
        'lng',
        'city_id',
    ];

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
