<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserBasketSchedule extends Model implements Sectionable
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        // 'category_id',
        'schedule_id',
        'name',
        'is_active',
        'start_date',
        'next_run_date',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'start_date'    => 'date',
        'next_run_date' => 'date',
    ];

    // ================= Relations =================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function category(): BelongsTo
    // {
    //     return $this->belongsTo(Category::class);
    // }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(UserBasketScheduleItem::class);
    }

    // ================= Helper =================
    // public function canAddProduct($product_id): bool
    // {
    //     return $product->category_id === $this->category_id;
    // }

    public function getNextRunDateAttribute()
    {
        if (!$this->start_date || !$this->schedule) {
            return null;
        }

        $startDate = Carbon::parse($this->start_date);
        $today = Carbon::today();

        if ($startDate->greaterThan($today)) {
            return $startDate;
        }

        $intervalDays = (int) $this->schedule->interval_days;

        if ($intervalDays <= 0) {
            return null;
        }

        $daysPassed = $startDate->diffInDays($today);
        $cycles = intdiv($daysPassed, $intervalDays) + 1;

        return $startDate->addDays($cycles * $intervalDays);
    }
    public function toSectionArray(): array
    {
        $totalPrice = $this->items?->sum(
            fn($item) => $item->price * $item->quantity
        ) ?? 0;

        $discountValue = $this->schedule?->discount_value ?? 0;
        $discountType  = $this->schedule?->discount_type ?? null;

        $discountAmount = 0;

        if ($discountValue > 0) {
            if ($discountType === 'percent') {
                $discountAmount = round($totalPrice * $discountValue / 100, 2);
            } else {
                $discountAmount = round(min($discountValue, $totalPrice), 2);
            }
        }

        $finalPrice = round($totalPrice - $discountAmount, 2);
        $itemsCount = $this->items?->count() ?? 0;

        return [
            'id'    => $this->id,

            // section basics
            'title' => $this->name,
            'desc'  => $this->category?->name,

            'image' => $this->category?->image_url,

            'price' => round($totalPrice, 2),
            'price_after_discount' => $finalPrice,
            'discount' => $discountValue,

            // 'top_badges' => [],
            // 'bottom_badges' => [],
            'items_count' => $itemsCount,

        ];
    }
}
