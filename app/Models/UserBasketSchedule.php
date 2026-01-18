<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserBasketSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

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
}
