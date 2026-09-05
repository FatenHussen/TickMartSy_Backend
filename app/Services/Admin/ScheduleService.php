<?php

namespace App\Services\Admin;

use App\Models\Schedule;
use App\Services\BaseService;
use App\Http\Resources\Admin\Schedule\OneResource;
use App\Http\Resources\Admin\Schedule\AllResource;
use App\Services\Base\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ScheduleService extends BaseService
{
    protected $model = Schedule::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'badges',
        'scheduleImages',
    ];

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
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
            unset($filters['is_active']);
        }

        if (!empty($filters['discount_type'])) {
            $query->where('discount_type', $filters['discount_type']);
            unset($filters['discount_type']);
        }

        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $query->where($key, $value);
            }
        }

        if (!empty($config['search'])) {
            $search = $config['search'];
            $locale = app()->getLocale();

            $query->where(function ($q) use ($search, $locale) {
                $q->where("name->$locale", 'LIKE', "%$search%")
                    ->orWhere("description->$locale", 'LIKE', "%$search%")
                    ->orWhere('interval_days', 'LIKE', "%$search%")
                    ->orWhere('discount_value', 'LIKE', "%$search%");
            });
        }

        if (!empty($config['sortField']) && in_array($config['sortField'], $this->sortableFields)) {
            $order = strtolower($config['sortOrder'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($config['sortField'], $order);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query;
    }

    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            $data = $this->mergeRequestUploads($data);
            $media = $this->extractMedia($data);
            $badges = $data['badges'] ?? [];
            unset($data['badges']);

            $schedule = $this->model::create($data);
            $this->syncMedia($schedule, $media);
            $this->syncBadges($schedule, $badges);

            return new $this->resource(
                $this->model::with($this->relations)->findOrFail($schedule->id)
            );
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $schedule = $this->model::findOrFail($id);
            $data = $this->mergeRequestUploads($data);
            $media = $this->extractMedia($data);
            $badges = array_key_exists('badges', $data) ? $data['badges'] : null;
            unset($data['badges']);

            foreach ($schedule->translatable as $field) {
                if (!isset($data[$field]) || !is_array($data[$field])) {
                    continue;
                }

                $incoming = array_filter(
                    $data[$field],
                    static fn ($value) => $value !== null
                );
                unset($data[$field]);

                if ($incoming === []) {
                    continue;
                }

                $schedule->setTranslations(
                    $field,
                    array_merge($schedule->getTranslations($field), $incoming)
                );
            }

            $schedule->fill($data);
            $schedule->save();
            $this->syncMedia($schedule, $media);

            if ($badges !== null) {
                $this->syncBadges($schedule, $badges);
            }

            return new $this->resource(
                $this->model::with($this->relations)->findOrFail($schedule->id)
            );
        });
    }

    public function delete($id): bool
    {
        $schedule = $this->model::findOrFail($id);
        $mediaService = new MediaService();

        if ($schedule->image && Storage::disk('public')->exists($schedule->image)) {
            Storage::disk('public')->delete($schedule->image);
        }

        $mediaService->deleteMany($schedule->scheduleImages()->get());
        $schedule->delete();

        return true;
    }

    /**
     * Form-request validated() sometimes drops files / nested badge arrays on multipart POST.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeRequestUploads(array $data): array
    {
        $request = request();
        if (!$request) {
            return $data;
        }

        if (!(($data['image'] ?? null) instanceof UploadedFile) && $request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $images = $data['images'] ?? null;
        $hasUploadedImages = is_array($images) && collect($images)->contains(fn ($file) => $file instanceof UploadedFile);
        if (!$hasUploadedImages && $request->hasFile('images')) {
            $files = $request->file('images');
            $data['images'] = is_array($files) ? $files : [$files];
        }

        if (!array_key_exists('badges', $data) && $request->exists('badges')) {
            $data['badges'] = $request->input('badges');
        }

        if (!array_key_exists('deleted_image_ids', $data) && $request->exists('deleted_image_ids')) {
            $data['deleted_image_ids'] = $request->input('deleted_image_ids');
        }

        return $data;
    }

    protected function extractMedia(array &$data): array
    {
        $media = [
            'image' => $data['image'] ?? null,
            'images' => $data['images'] ?? null,
            'deleted_image_ids' => $data['deleted_image_ids'] ?? null,
        ];

        unset($data['image'], $data['images'], $data['deleted_image_ids']);

        return $media;
    }

    protected function syncMedia(Schedule $schedule, array $media): void
    {
        $mediaService = new MediaService();

        if ($media['image'] instanceof UploadedFile) {
            if ($schedule->image && Storage::disk('public')->exists($schedule->image)) {
                Storage::disk('public')->delete($schedule->image);
            }

            $path = $media['image']->store('schedules', 'public');
            $schedule->update(['image' => $path]);
        }

        if (!empty($media['deleted_image_ids']) && is_array($media['deleted_image_ids'])) {
            $ids = array_values(array_unique(array_map('intval', $media['deleted_image_ids'])));
            $toDelete = $schedule->scheduleImages()->whereIn('id', $ids)->get();
            $mediaService->deleteMany($toDelete);
        }

        if (!empty($media['images']) && is_array($media['images'])) {
            $mediaService->uploadMultiple($schedule, $media['images'], 'schedule');
        }
    }

    protected function syncBadges(Schedule $schedule, array $badges): void
    {
        $sync = [];

        foreach ($badges as $badge) {
            if (is_array($badge) && isset($badge['id'])) {
                $sync[(int) $badge['id']] = [
                    'position' => $badge['position'] ?? 'top',
                ];
            } elseif (is_numeric($badge)) {
                $sync[(int) $badge] = ['position' => 'top'];
            }
        }

        $schedule->badges()->sync($sync);
    }
}
