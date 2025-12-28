<?php

namespace App\Services\Base;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    protected string $disk = 'public';

    /* =========================================================
     | Upload
     ========================================================= */

    public function upload(
        Model $model,
        UploadedFile $file,
        string $collection = 'default',
        ?int $order = null
    ): Media {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs("media/{$collection}", $fileName, $this->disk);

        $order ??= ($model->media()
            ->where('collection', $collection)
            ->max('order') ?? -1) + 1;

        return $model->media()->create([
            'collection' => $collection,
            'path'  => $filePath,
            'order'      => $order,
        ]);
    }

    public function uploadMultiple(
        Model $model,
        array $files,
        string $collection = 'default'
    ): Collection {
        return collect($files)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->map(fn ($file) => $this->upload($model, $file, $collection));
    }

    /* =========================================================
     | Delete
     ========================================================= */

    public function delete(Media $media): bool
    {
        Storage::disk($this->disk)->delete($media->file_path);
        return $media->delete();
    }

    public function deleteMany(iterable $mediaItems): int
    {
        return DB::transaction(function () use ($mediaItems) {
            $count = 0;

            foreach ($mediaItems as $media) {
                if ($this->delete($media)) {
                    $count++;
                }
            }

            return $count;
        });
    }

    public function deleteByCollection(Model $model, string $collection): int
    {
        return $this->deleteMany(
            $model->media()->where('collection', $collection)->get()
        );
    }

    /* =========================================================
     | Replace
     ========================================================= */

    public function replace(Media $media, UploadedFile $file): Media
    {
        $order      = $media->order;
        $collection = $media->collection;
        $model      = $media->mediable;

        $this->delete($media);

        return $this->upload($model, $file, $collection, $order);
    }

    /* =========================================================
     | Read
     ========================================================= */

    public function getCollection(Model $model, string $collection): Collection
    {
        return $model->media()
            ->where('collection', $collection)
            ->orderBy('order')
            ->get();
    }

    public function getFirst(Model $model, string $collection): ?Media
    {
        return $model->media()
            ->where('collection', $collection)
            ->orderBy('order')
            ->first();
    }

    /* =========================================================
     | Ordering
     ========================================================= */

    public function setOrder(Model $model, string $collection, array $orderedIds): void
    {
        DB::transaction(function () use ($model, $collection, $orderedIds) {
            foreach ($orderedIds as $index => $id) {
                $model->media()
                    ->where('collection', $collection)
                    ->where('id', $id)
                    ->update(['order' => $index]);
            }
        });
    }
}
