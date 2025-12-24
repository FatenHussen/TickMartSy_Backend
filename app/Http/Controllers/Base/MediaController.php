<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Base\MediaService;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'type'          => 'required|string',   // store, user
            'id'            => 'required|integer',
            'collection'    => 'required|string',
            'ordered_ids'   => 'required|array',
            'ordered_ids.*' => 'integer',
        ]);

        $types = [
            'store' => \App\Models\Store::class,
            'user'  => \App\Models\User::class,
        ];

        if (!isset($types[$data['type']])) {
            return response()->json(['message' => 'Invalid type'], 422);
        }

        $modelClass = $types[$data['type']];
        $model = $modelClass::find($data['id']);

        if (!$model) {
            return response()->json(['message' => 'Model not found'], 404);
        }

        $mediaIds = $model->media()
            ->where('collection', $data['collection'])
            ->pluck('id')
            ->toArray();

        if (array_diff($data['ordered_ids'], $mediaIds)) {
            return response()->json([
                'message' => 'Some media items do not belong to this model'
            ], 422);
        }

        $this->mediaService->setOrder(
            $model,
            $data['collection'],
            $data['ordered_ids']
        );

        return response()->json([
            'message' => 'Media reordered successfully'
        ]);
    }
}
