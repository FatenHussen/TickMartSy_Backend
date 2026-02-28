<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            $model->logActivity('created');
        });

        static::updated(function (Model $model) {
            $model->logActivity('updated');
        });

        static::deleted(function (Model $model) {
            $model->logActivity('deleted');
        });
    }

    protected function logActivity(string $action)
    {
        if (!auth()->check()) return;

        $changes = null;

        if ($action === 'updated') {
            $dirty = collect($this->getChanges())->except(['updated_at', 'created_at']);

            if ($dirty->isNotEmpty()) {
                $changes = $dirty->mapWithKeys(fn($new, $key) => [
                    $key => [
                        'old' => $this->decodeIfJson($this->getOriginal($key)),
                        'new' => $this->decodeIfJson($new),
                    ]
                ])->toArray();
            }
        }

        ActivityLog::create([
            'performed_by_type' => get_class(auth()->user()),
            'performed_by_id'   => auth()->id(),
            'action'            => $action,
            'model_type'        => get_class($this),
            'model_id'          => $this->id,
            'changes'           => $changes,
        ]);
    }

    private function decodeIfJson($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) return $decoded;
        }
        return $value;
    }
}
