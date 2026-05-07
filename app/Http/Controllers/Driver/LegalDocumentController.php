<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\LegalDocumentResource;
use App\Models\LegalDocument;
use Illuminate\Http\Request;

class LegalDocumentController extends Controller
{
    public function index()
    {
        $documents = LegalDocument::query()
            ->where('key', 'like', '%driver%')
            ->get();

        return $this->sendResponse(data: LegalDocumentResource::collection($documents));
    }

    public function show(Request $request, string $key)
    {
        if (!str_contains($key, 'driver')) {
            return $this->sendError(message: __('custom.documents.not_found'), code: 404);
        }

        $document = LegalDocument::where('key', $key)->first();

        if (!$document) {
            return $this->sendError(message: __('custom.documents.not_found'), code: 404);
        }

        return $this->sendResponse(data: new LegalDocumentResource($document));
    }
}
