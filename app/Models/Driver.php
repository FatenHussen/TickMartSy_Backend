<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Driver extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $fillable = [
        'phone',
        'password',
        'name',
        'is_active',
        'address',
        'status',
        'rate_per_order',
        'image',
        'vehicle_type',
        'vehicle_number',
    ];
    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'password' => 'hashed',
    ];
    public function areas()
    {
        return $this->belongsToMany(Area::class, 'area_driver');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function completedOrders()
    {
        return $this->hasMany(Order::class)->where('order_status', 'completed');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1);
    }

    public function getTotalEarningsAttribute(): float
    {
        return (float) $this->completedOrders()->sum('delivery_price');
    }
}
