<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function markAsRead(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'notification_id' => ['required', 'uuid'],
        ]);

        $notification = $user->notifications()
            ->where('id', $validated['notification_id'])
            ->first();

        if (!$notification) {
            throw new NotFoundException();
        }

        if (is_null($notification->read_at)) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return $this->sendResponse();
    }

    public function notifications(Request $request)
    {
        $user = auth()->user();

        $query = $user->notifications()->latest();

        if ($request->has('read')) {
            if ($request->boolean('read')) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        if ($request->filled('target_page')) {
            $query->where('data->data->type', 'admin')
                ->where('data->data->is_fixed', 1)
                ->whereNull('read_at')
                ->where('data->data->target_page', $request->input('target_page'));
        }

        $notifications = $query->paginate(20);

        return $this->sendResponse(
            data: NotificationResource::collection($notifications)
        );
    }

    public function markAllAsRead()
    {
        $user = auth()->user();

        $user->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        return $this->sendResponse();
    }
}
