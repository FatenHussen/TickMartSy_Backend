<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $result = ActivityLog::with('performedBy')
            ->when($request->action, function ($q) use ($request) {
                $q->where('action', $request->action);
            })
            ->when($request->model, function ($q) use ($request) {
                $q->where('model_type', $request->model);
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return [
            'items' => collect($result->items())->map(fn($log) => [
                'id'        => $log->id,
                'user'      => $log->performedBy?->name ?? 'System',
                'user_type' => $log->performed_by_type ? class_basename($log->performed_by_type) : 'System',
                'action'    => ucfirst($log->action),
                'model'     => class_basename($log->model_type),
                'model_id'  => $log->model_id,
                'changes'   => $log->changes,
                'date'      => $log->created_at->format('Y-m-d H:i'),
                'message'   => $this->buildMessage($log),
            ]),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'per_page'     => $result->perPage(),
                'total'        => $result->total(),
            ],
        ];
    }

    private function buildMessage($log): string
    {
        $user  = $log->performedBy?->name ?? 'System';
        $model = class_basename($log->model_type);

        return match ($log->action) {
            'created' => "{$user} created {$model} #{$log->model_id}",
            'updated' => "{$user} updated {$model} #{$log->model_id}",
            'deleted' => "{$user} deleted {$model} #{$log->model_id}",
            default   => "{$user} performed {$log->action} on {$model} #{$log->model_id}",
        };
    }
}
