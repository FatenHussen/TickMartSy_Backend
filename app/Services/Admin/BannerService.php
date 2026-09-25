<?php

namespace App\Services\Admin;

use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Banner\AllResource;
use App\Http\Resources\Banner\OneResource;
use App\Models\Banner;

class BannerService extends BaseService
{
    public function __construct(Banner $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->searchableFields = ['title', 'description'];
        $this->sortableFields   = ['id', 'order'];
        $this->pagination = true;
        // $this->mediaCollections = [
        //     'image' => [
        //         'collection' => 'logo',
        //         'type'       => 'single',
        //     ],
        // ];

        $this->singleImages = [
            'image'
        ];
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();

        $object = $this->model::query()->findOrFail($id);
        $data = $this->withPresentRequestFields($data);
        $clears = [];

        foreach ($object->translatable as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $incoming = $data[$field];
            $cleaned = is_array($incoming)
                ? $this->cleanedTranslations($object->getTranslations($field), $incoming)
                : [];

            $clears[$field] = $cleaned === []
                ? null
                : json_encode($cleaned, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            unset($data[$field]);
        }

        foreach (['link', 'expires_at'] as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];
            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === null || $value === '') {
                $clears[$field] = null;
                unset($data[$field]);
            }
        }

        $this->handleSingleImages($object, $data);

        if ($data !== []) {
            $object->update($data);
        }

        if ($clears !== []) {
            $this->model::query()->whereKey($object->id)->update($clears);
        }

        $object->refresh();

        DB::commit();

        return new $this->resource($object);
    }

    /**
     * Empty multipart values are present keys. Keep them even when validation drops nulls.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withPresentRequestFields(array $data): array
    {
        $request = request();
        if ($request === null) {
            return $data;
        }

        foreach (['title', 'description', 'button_text', 'link', 'expires_at'] as $field) {
            if ($request->exists($field)) {
                $data[$field] = $request->input($field);
            }
        }

        return $data;
    }

    /**
     * Blank locales are removed. A field with no remaining locales is stored as NULL.
     *
     * Locales omitted from the payload stay as they are. A blank locale is removed.
     *
     * @param  array<string, string>  $existing
     * @param  array<string, mixed>  $incoming
     * @return array<string, string>
     */
    private function cleanedTranslations(array $existing, array $incoming): array
    {
        $cleaned = $existing;

        foreach ($incoming as $locale => $value) {
            if (! is_string($locale)) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === null || $value === '') {
                unset($cleaned[$locale]);
                continue;
            }

            $cleaned[$locale] = $value;
        }

        return $cleaned;
    }
}
