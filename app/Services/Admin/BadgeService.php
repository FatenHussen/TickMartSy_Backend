<?php

namespace App\Services\Admin;

use App\Http\Resources\Badge\AdminOneResource;
use App\Http\Resources\Badge\OneResource;
use App\Models\Badge;
use App\Services\BaseService;
use Illuminate\Http\UploadedFile;

class BadgeService extends BaseService
{
    public function __construct(Badge $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = OneResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id'];
        $this->singleImages = ['image'];
    }

    public function create($data)
    {
        $data['type'] = $this->typeFromImage($data['image'] ?? null) ?? 'text';

        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $type = $this->typeFromImage($data['image'] ?? null);
        if ($type) {
            $data['type'] = $type;
        }

        return parent::update($id, $data);
    }

    protected function typeFromImage(?UploadedFile $image): ?string
    {
        if (!$image) {
            return null;
        }

        $extension = strtolower($image->getClientOriginalExtension());
        $mime = $image->getMimeType();

        if ($extension === 'gif' || str_contains($mime ?? '', 'gif')) {
            return 'gif';
        }

        return 'image';
    }
}
