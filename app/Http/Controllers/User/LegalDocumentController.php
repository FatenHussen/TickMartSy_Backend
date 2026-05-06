<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\LegalDocumentResource;
use Illuminate\Http\Request;
use App\Models\LegalDocument;

class LegalDocumentController extends Controller
{
    public function index()
    {
        $documents = LegalDocument::query()
            ->whereNotIn('key', ['terms_conditions', 'marketer_terms_conditions'])
            ->get();

        return $this->sendResponse(data: LegalDocumentResource::collection($documents));
    }

    public function show(Request $request, $key)
    {
        $document = LegalDocument::where('key', $key)->first();

        if (!$document) {
            return $this->sendError(message: __('custom.documents.not_found'), code: 404);
        }
        return $this->sendResponse(data: new LegalDocumentResource($document));
    }
}
