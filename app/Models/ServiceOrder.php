<?php

namespace App\Models;

use App\Enums\ServiceOrderStatus;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrder extends Model
{
    protected $fillable = [
        'user_id',
        'shop_id',
        'vendor_service_id',
        'shop_vendor_service_id',
        'price',
        'price_unit',
        'status',
        'notes',
        'date',
        'time',
    ];

    protected $casts = [
        'price' => 'float',
        'date' => 'date',
    ];

    protected $attributes = [
        'status' => ServiceOrderStatus::PENDING->value,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function vendorService(): BelongsTo
    {
        return $this->belongsTo(VendorService::class);
    }

    public function shopVendorService(): BelongsTo
    {
        return $this->belongsTo(ShopVendorService::class);
    }

    public function formattedOrderTime(): ?string
    {
        if ($this->time === null) {
            return null;
        }
        if ($this->time instanceof DateTimeInterface) {
            return $this->time->format('H:i');
        }
        $s = (string) $this->time;

        return strlen($s) >= 5 ? substr($s, 0, 5) : $s;
    }
}
