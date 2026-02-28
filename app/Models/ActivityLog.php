<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Container\Attributes\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log as FacadesLog;

class ActivityLog extends Model
{
    // use LogsActivity;
    protected $fillable = [
        'performed_by_type',
        'performed_by_id',
        'action',
        'model_type',
        'model_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function performedBy()
    {
        return $this->morphTo();
    }


    // public function getFormattedChangesAttribute(): ?string
    // {
    //     if (!$this->attributes['changes'] || empty($this->attributes['changes'])) {
    //         return null;
    //     }

    //     return collect($this->attributes['changes'])
    //         ->map(function ($value, $field) {

    //             $old = $value['old'] ?? '—';
    //             $new = $value['new'] ?? '—';

    //             if (is_array($old)) $old = json_encode($old);
    //             if (is_array($new)) $new = json_encode($new);

    //             return "{$field}: {$old} → {$new}";
    //         })
    //         ->implode("\n");
    // }
}
