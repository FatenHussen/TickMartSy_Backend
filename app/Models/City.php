<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasTranslations, LogsActivity;
    protected $fillable = ['name', 'governorate_id', 'is_active'];
    public $translatable = ['name'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
