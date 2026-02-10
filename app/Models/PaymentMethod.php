<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'icon',
        'is_active',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];
    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->icon);
    }
}
