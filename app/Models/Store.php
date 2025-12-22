<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Translatable\HasTranslations;

class Store extends Authenticatable
{
    use HasFactory, HasTranslations;

    public $translatable = ['store_name', 'description', 'store_address'];

    protected $fillable = [
        'name',
        'email',
        'owner_phone',
        'password',
        'store_name',
        'description',
        'logo',
        'cover',
        'store_phone',
        'store_email',
        'store_address',
        'area_id',
        'lat',
        'lng',
        'status',
        'rating',
        'category_id',
        'time_work',
        'date_contract',
        'duration_contract',
        'contract_number',
        'Commercial_registration_number',
        'agreed_percentage',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'time_work' => 'array',
        'date_contract' => 'date',
        'duration_contract' => 'time',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
