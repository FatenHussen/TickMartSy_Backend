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

        $this->imageColumn  = 'flag_icon';
        $this->imageFolder  = 'flags';
    }

    /* ================= Language Files ================= */

    public function createLangFiles(string $code): void
    {
        $langPath = resource_path("lang/{$code}");

        if (!File::exists($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }

        $defaultFiles = [
            'auth.php' => "<?php\n\nreturn [\n    'failed' => 'These credentials do not match our records.',\n];",
            'pagination.php' => "<?php\n\nreturn [\n    'previous' => '&laquo; Previous',\n    'next' => 'Next &raquo;',\n];",
            'validation.php' => "<?php\n\nreturn [\n    'required' => 'The :attribute field is required.',\n];",
            'custom.php' => "<?php\n\nreturn [];",
            'filament.php' => "<?php\n\nreturn [];",
        ];

        foreach ($defaultFiles as $file => $content) {
            $filePath = $langPath . '/' . $file;

            if (!File::exists($filePath)) {
                File::put($filePath, $content);
            }
        }

        $jsonPath = resource_path("lang/{$code}.json");
        if (!File::exists($jsonPath)) {
            File::put(
                $jsonPath,
                json_encode(new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }
    }

    /* ================= Override Create ================= */

    public function create(array $data)
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
