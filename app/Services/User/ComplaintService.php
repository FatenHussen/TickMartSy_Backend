<?php

namespace App\Services\User;


use App\Models\Complaint;
use App\Enums\ComplaintStatus;

class ComplaintService
{
    public function store(array $data): Complaint
    {
        $paths = [];

        if (!empty($data['images'])) {
            foreach ($data['images'] as $image) {
                $paths[] = $image->store('complaints', 'public');
            }
        }

        return Complaint::create([
            'user_id'  => auth('user')->id() ?? 1,
            'order_id' => $data['order_id'],
            'message'  => $data['message'],
            'type'  => $data['type'],
            'images'   => $paths, // 👈 array
            'status'   => ComplaintStatus::NEW,
        ]);
    }

    public function index(array $filters = [])
    {
        return Complaint::where('user_id', auth('user')->id())
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['order_id'] ?? null, function ($q, $orderId) {
                $q->where('order_id', $orderId);
            })
            ->when($filters['from'] ?? null, function ($q, $from) {
                $q->whereDate('created_at', '>=', $from);
            })
            ->when($filters['to'] ?? null, function ($q, $to) {
                $q->whereDate('created_at', '<=', $to);
            })
            ->latest()
            ->get();
    }
}
