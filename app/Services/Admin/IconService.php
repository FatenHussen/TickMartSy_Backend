<?php

namespace App\Services\Admin;

use App\Models\Icon;
use App\Services\BaseService;
use App\Http\Resources\Icon\IconResource;
use Illuminate\Support\Facades\Storage;

class IconService extends BaseService
{
    protected $model = Icon::class;
    protected $resource = IconResource::class;
    protected $collection = IconResource::class;
    protected $searchableFields = ['name', 'description'];
    protected $sortableFields = ['id', 'name', 'is_active', 'created_at'];

    public function create(array $data)
    {
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $icon = Icon::findOrFail($id);

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old image
            if ($icon->image && Storage::disk('public')->exists($icon->image)) {
                Storage::disk('public')->delete($icon->image);
            }

            $data['image'] = $this->uploadImage($data['image']);
        }

        return parent::update($id, $data);
    }

    public function delete($id)
    {
        $icon = Icon::findOrFail($id);

        // Delete image
        if ($icon->image && Storage::disk('public')->exists($icon->image)) {
            Storage::disk('public')->delete($icon->image);
        }

        return parent::delete($id);
    }

    protected function uploadImage($file): string
    {
        $path = $file->store('icons', 'public');
        return 'storage/' . $path;
    }
}
