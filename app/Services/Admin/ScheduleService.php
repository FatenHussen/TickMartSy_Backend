<?php

namespace App\Services\Admin;

use App\Models\Schedule;
use App\Services\BaseService;
use App\Http\Resources\Admin\Schedule\OneResource;
use App\Http\Resources\Admin\Schedule\AllResource;

class ScheduleService extends BaseService
{
    protected $model = Schedule::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $searchableFields = [
        'name',
        'interval_days',
        'discount_type',
        'discount_value',
    ];

    protected $sortableFields = [
        'id',
        'name',
        'interval_days',
        'discount_value',
        'created_at',
    ];

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by is_active
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
            unset($filters['is_active']);
        }

        // Filter by discount_type
        if (!empty($filters['discount_type'])) {
            $query->where('discount_type', $filters['discount_type']);
            unset($filters['discount_type']);
        }

        // Apply remaining filters
        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $query->where($key, $value);
            }
        }

        // Search functionality
        if (!empty($config['search'])) {
            $search = $config['search'];
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                $q->where("name->$locale", 'LIKE', "%$search%")
                  ->orWhere('interval_days', 'LIKE', "%$search%")
                  ->orWhere('discount_value', 'LIKE', "%$search%");
            });
        }

        // Sorting
        if (!empty($config['sortField']) && in_array($config['sortField'], $this->sortableFields)) {
            $order = strtolower($config['sortOrder'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($config['sortField'], $order);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query;
    }
}
