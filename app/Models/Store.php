<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Store extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'owner_name',
        'owner_email',
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

    protected $casts = [
        'time_work' => 'array',
        'date_contract' => 'date',
        'duration_contract' => 'time',
    ];

    protected $hidden = [
        'password',
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
