<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledBasketAlert extends Model
{
    public const TYPE_STOCK_ISSUE = 'stock_issue';

    public const STATUS_OPEN = 'open';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public const DECISION_ACCEPT_PARTIAL = 'accept_partial';
    public const DECISION_WAIT_FULL = 'wait_full';
    public const DECISION_SKIP_CYCLE = 'skip_cycle';
    public const DECISION_DISMISSED = 'dismissed';

    public const BASKET_TYPE_USER_SCHEDULE = 'user_schedule';
    public const BASKET_TYPE_ADMIN_SCHEDULE = 'admin_schedule';

    protected $fillable = [
        'user_id',
        'basket_type',
        'basket_reference_id',
        'basket_schedule_id',
        'order_id',
        'alert_type',
        'status',
        'user_decision',
        'payload',
        'next_run_date',
        'first_detected_at',
        'last_detected_at',
        'last_notified_at',
        'resolved_at',
        'last_resolution_notified_at',
        'decision_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'next_run_date' => 'date',
        'first_detected_at' => 'datetime',
        'last_detected_at' => 'datetime',
        'last_notified_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_resolution_notified_at' => 'datetime',
        'decision_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function basketSchedule(): BelongsTo
    {
        return $this->belongsTo(BasketSchedule::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }
}
