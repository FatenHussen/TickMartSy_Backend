<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasTranslations, LogsActivity;
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
