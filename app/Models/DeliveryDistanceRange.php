<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class DeliveryDistanceRange extends Model
{
    protected $fillable = [
        'min_distance',
        'max_distance',
        'multiplier',
    ];

    protected $casts = [
        'min_distance' => 'float',
        'max_distance' => 'float',
        'multiplier' => 'float',
    ];

    public function scopeSorted(Builder $query): Builder
    {
        return $query
            ->orderBy('min_distance')
            ->orderByRaw('CASE WHEN max_distance IS NULL THEN 1 ELSE 0 END')
            ->orderBy('max_distance')
            ->orderBy('id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $range): void {
            $min = (float)$range->min_distance;
            $max = $range->max_distance !== null ? (float)$range->max_distance : null;
            $multiplier = (float)$range->multiplier;

            if ($min < 0) {
                throw ValidationException::withMessages([
                    'min_distance' => 'الحد الأدنى للمسافة لا يمكن أن يكون سالباً.',
                ]);
            }

            if ($max !== null && $max <= $min) {
                throw ValidationException::withMessages([
                    'max_distance' => 'الحد الأعلى للمسافة يجب أن يكون أكبر من الحد الأدنى.',
                ]);
            }

            if ($multiplier <= 0) {
                throw ValidationException::withMessages([
                    'multiplier' => 'معامل الضرب يجب أن يكون أكبر من صفر.',
                ]);
            }

            if (self::hasOverlap($min, $max, $range->id)) {
                throw ValidationException::withMessages([
                    'range' => 'يوجد تعارض في المسافات مع Range آخر',
                ]);
            }
        });
    }

    public static function hasOverlap(float $min, ?float $max, ?int $ignoreId = null): bool
    {
        return self::query()
            ->when($ignoreId, fn(Builder $query) => $query->whereKeyNot($ignoreId))
            ->where(function (Builder $query) use ($min): void {
                $query->whereNull('max_distance')
                    ->orWhere('max_distance', '>', $min);
            })
            ->when(
                $max !== null,
                fn(Builder $query) => $query->where('min_distance', '<', $max)
            )
            ->exists();
    }
}
