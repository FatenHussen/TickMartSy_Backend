<?php

namespace App\Models;

use App\Enums\ServiceOrderStatus;
use App\Models\Concerns\AppliesAreaScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrder extends Model
{
    use AppliesAreaScope;
    protected static array $areaRelationPaths = ['user'];

    protected $fillable = [
        'user_id',
        'shop_id',
        'vendor_service_id',
        'shop_vendor_service_id',
        'price',
        'price_unit',
        'status',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
}
