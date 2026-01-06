<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasTranslations;
    protected $fillable = ['name', 'governorate_id'];
    public $translatable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
