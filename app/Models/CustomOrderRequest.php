<?php

namespace App\Models;

use App\Enums\CustomOrderRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomOrderRequest extends Model
{
    protected $fillable = [
        'user_id',
        'user_address_id',
        'payment_method_id',
        'order_id',
        'description',
        'images',
        'expected_at',
        'status',
        'admin_note',
        'rejection_reason',
    ];

    protected $casts = [
        'images' => 'array',
        'expected_at' => 'datetime',
        'status' => CustomOrderRequestStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function imageUrls(): array
    {
        return array_map(
            fn ($path) => asset('storage/' . ltrim((string) $path, '/')),
            $this->images ?? []
        );
    }
}
