<?php

namespace App\Models;

use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class PromotionRequest extends Model
{
    use SoftDeletes, HasTranslations, LogsActivity;

    public $translatable = ['title', 'description'];

    protected $fillable = [
        'vendor_id',
        'shop_id',
        'type',
        'title',
        'description',
        'images',
        'discount_percentage',
        'offer_starts_at',
        'offer_ends_at',
        'banner_position',
        'link_url',
        'banner_starts_at',
        'banner_ends_at',
        'status',
        'admin_notes',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'images' => 'array',
        'discount_percentage' => 'decimal:2',
        'offer_starts_at' => 'date',
        'offer_ends_at' => 'date',
        'banner_starts_at' => 'date',
        'banner_ends_at' => 'date',
        'approved_at' => 'datetime',
        'type' => PromotionType::class,
        'status' => PromotionStatus::class,
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function isExpired(): bool
    {
        $endDate = $this->type === PromotionType::OFFER
            ? $this->offer_ends_at
            : $this->banner_ends_at;

        return $endDate && $endDate->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('status', PromotionStatus::APPROVED)
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->where('type', PromotionType::OFFER)
                        ->where('offer_ends_at', '>=', now());
                })->orWhere(function ($sq) {
                    $sq->where('type', PromotionType::BANNER)
                        ->where('banner_ends_at', '>=', now());
                });
            });
    }
}
