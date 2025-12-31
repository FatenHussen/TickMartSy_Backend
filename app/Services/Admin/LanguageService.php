<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Language\OneResource;
use App\Http\Resources\Admin\Language\AllResource;
use App\Models\Language;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LanguageService extends BaseService
{
    public function __construct(Language $model)
    {
        $this->model        = $model;
        $this->resource     = OneResource::class;
        $this->collection   = AllResource::class;
        $this->pagination = true;

        $this->imageColumn  = 'flag_icon';
        $this->imageFolder  = 'flags';
    }

    /* ================= Language Files ================= */

    public function createLangFiles(string $code): void
    {
        $sourceLang = 'en';

        $sourcePath = resource_path("lang/{$sourceLang}");
        $targetPath = resource_path("lang/{$code}");

        if (!File::exists($sourcePath)) {
            throw new \Exception("Source language [{$sourceLang}] does not exist.");
        }

        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }

        foreach (File::files($sourcePath) as $file) {
            $targetFile = $targetPath . '/' . $file->getFilename();

            if (!File::exists($targetFile)) {
                File::copy($file->getPathname(), $targetFile);
            }
        }

        $sourceJson = resource_path("lang/{$sourceLang}.json");
        $targetJson = resource_path("lang/{$code}.json");

        if (File::exists($sourceJson) && !File::exists($targetJson)) {
            File::copy($sourceJson, $targetJson);
        }
    }
    /* ================= Override Create ================= */

    public function create($data)
    {
        DB::beginTransaction();

        try {
            if (!empty($data['is_default']) && $data['is_default']) {
                $this->model->where('is_default', true)->update(['is_default' => false]);
            }

            
            $language = $this->model::create($data);

            $this->handleImages($language, $data);

            $this->createLangFiles($language->code);

            DB::commit();

            return new $this->resource($language);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
