<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'label',
        'area_id',
        'street_name',
        'nearest_landmark',
        'building_number',
        'floor_apartment',
        'contact_phone',
        'lat',
        'lng',
        'is_default',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get full address as a formatted string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_name,
            $this->building_number ? "Building: {$this->building_number}" : null,
            $this->floor_apartment ? "Floor/Apt: {$this->floor_apartment}" : null,
            $this->nearest_landmark ? "Near: {$this->nearest_landmark}" : null,
            $this->area?->name,
        ]);

        return implode(', ', $parts);
    }
}
