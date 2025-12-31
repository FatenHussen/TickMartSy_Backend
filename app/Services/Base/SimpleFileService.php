<?php

namespace App\Services\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SimpleFileService
{
    protected string $disk = 'public';

    protected function baseFolder(Model $model): string
    {
        return  Str::kebab(class_basename($model));
    }

    public function upload(
        Model $model,
        ?string $oldPath,
        UploadedFile $file,
    ): string {

        if ($oldPath) {
            Storage::disk($this->disk)->delete($oldPath);
        }

        $folder = $this->baseFolder($model);

        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $name, $this->disk);
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk($this->disk)->delete($path);
        }
    }
}
