<?php

// app/Models/PointRule.php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointRule extends Model
{
    use LogsActivity;
    protected $fillable = [
        'code',
        'title',
        'type',
        'value',
        'min_order_amount',
        'expires_after_days',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }
}
